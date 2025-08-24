<?php

namespace Fileknight\Service\File\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class FileNotFoundException extends ApiException
{
    public function __construct(string $fileId)
    {
        parent::__construct(
            'FILE_NOT_FOUND',
            'Required file was not found',
            Response::HTTP_NOT_FOUND,
            ['fileId' => $fileId]
        );
    }
}
