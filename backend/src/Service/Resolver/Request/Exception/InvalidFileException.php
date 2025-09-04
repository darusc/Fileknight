<?php

namespace Fileknight\Service\Resolver\Request\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception is thrown when an uploaded file is invalid (UPLOAD_ERR_XXX error)
 */
class InvalidFileException extends ApiException
{
    public function __construct(UploadedFile $file)
    {
        parent::__construct(
            'INVALID_FILE',
            $file->getErrorMessage() . ' ' . $file->getError(),
            Response::HTTP_BAD_REQUEST,
            ['file' => $file->getClientOriginalName(),]
        );
    }
}
