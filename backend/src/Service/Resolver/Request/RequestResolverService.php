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
     * @param array $files The required files. If missing throw IncompleteRequestException
     * @return RequestDataDTO
     *
     * @throws IncompleteRequestException
     * @throws InvalidJsonException
     * @throws InvalidFileException
     * @throws UnsupportedContentTypeException
     */
    public function resolve(Request $request, array $required, array $optional = [], array $files = []): RequestDataDTO
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

        // Check files (always required)
        $missingFiles = [];
        foreach ($files as $key) {
            /** @var UploadedFile|null $uploadedFile */
            $file = $request->files->get($key);

            if ($file === null) {
                $missingFiles[] = $key;
            } elseif (!$file->isValid()) {
                throw new InvalidFileException($file);
            } else {
                $requestData->add($key, $file);
            }
        }

        // Throw if any required field/file is missing
        if (count($missingFields) > 0 || count($missingFiles) > 0) {
            throw new IncompleteRequestException($missingFields, $missingFiles);
        }

        // Add optional fields (default to null if not present)
        foreach ($optional as $field) {
            if (array_key_exists($field, $data)) {
                $requestData->add($field, $data[$field]);
            }
        }

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
