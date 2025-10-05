<?php

namespace Fileknight\Service\Resolver\Request;

use Fileknight\DTO\RequestDataDTO;
use Fileknight\Service\Resolver\Request\Exception\IncompleteRequestException;
use Fileknight\Service\Resolver\Request\Exception\InvalidFileException;
use Fileknight\Service\Resolver\Request\Exception\InvalidJsonException;
use Fileknight\Service\Resolver\Request\Exception\UnsupportedContentTypeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class that parses the request and extracts the given
 * fields with support for required and optional fields
 */
class RequestResolverService
{
    /**
     * Extract all given fields from the given request object
     *
     * @param Request $request
     * @param array $required The required fields. Missing or empty fields will throw IncompleteRequestException
     * @param array $optional The optional fields. Not throwing exception if it doesn't exist
     * @param bool $files If true and files are missing throw IncompleteRequestException
     * @return RequestDataDTO
     *
     * @throws IncompleteRequestException
     * @throws InvalidFileException
     * @throws InvalidJsonException
     * @throws UnsupportedContentTypeException
     */
    public function resolve(Request $request, array $required, array $optional = [], bool $files = false): RequestDataDTO
    {
        $data = $this->getDataFromRequest($request);
        $requestData = new RequestDataDTO();

        // Check required fields
        $missingFields = [];
        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                $missingFields[] = $field;
            } else {
                $requestData->add($field, $data[$field]);
            }
        }

        /** @var UploadedFile[] $uploadedFiles */
        $uploadedFiles = [];
        if ($files) {
            $uploadedFiles = $request->files->get('files', []);
            /** @var UploadedFile $uploadedFile */
            foreach ($uploadedFiles as $uploadedFile) {
                if (!$uploadedFile->isValid()) {
                    throw new InvalidFileException($uploadedFile);
                }

                $requestData->addFile($uploadedFile);
            }
        }

        // Throw if any required field/file is missing
        if (count($missingFields) > 0 || ($files && count($uploadedFiles) == 0)) {
            throw new IncompleteRequestException($missingFields, ['files']);
        }

        // Add optional fields (default to null if not present)
        foreach ($optional as $field) {
            if (array_key_exists($field, $data)) {
                $requestData->add($field, $data[$field]);
            }
        }

        // Normalize 'null' to null (this is done for ease of development
        // as Postman sends only strings in multipart/form-data)
        $requestData->normalize();

        return $requestData;
    }

    /**
     * @throws InvalidJsonException
     * @throws UnsupportedContentTypeException
     */
    private function getDataFromRequest(Request $request): ?array
    {
        if ($this->isJson($request)) {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidJsonException(json_last_error_msg());
            }
        } elseif ($this->isGet($request)) {
            $data = $request->query->all();
        } elseif ($this->isForm($request)) {
            $data = $request->request->all();
        } else {
            throw new UnsupportedContentTypeException($request->headers->get('Content-Type', ''));
        }

        return $data;
    }

    private function isJson(Request $request): bool
    {
        return str_starts_with($request->headers->get('Content-Type', ''), 'application/json');
    }

    private function isForm(Request $request): bool
    {
        return str_starts_with($request->headers->get('Content-Type', ''), 'multipart/form-data');
    }

    private function isGet(Request $request): bool
    {
        return $request->isMethod('GET');
    }
}
