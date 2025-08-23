<?php

namespace Fileknight\Service\Access\Exception;

use Fileknight\Entity\Directory;
use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class FolderAccessDeniedException extends ApiException
{
    public function __construct(Directory $directory)
    {
        parent::__construct(
            'FOLDER_ACCESS_DENIED',
            'Access denied to required folder',
            Response::HTTP_FORBIDDEN,
            ['folderId' => $directory->getId()]
        );
    }
}
