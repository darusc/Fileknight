<?php

namespace Fileknight\Service\Access\Exception;

use Fileknight\Entity\File;
use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class FileAccessDeniedException extends ApiException
{
    public function __construct(File $file)
    {
        parent::__construct(
            'FILE_ACCESS_DENIED',
            'Access denied to required file',
            Response::HTTP_FORBIDDEN,
            ['fileId' => $file->getId()]
        );
    }
}
