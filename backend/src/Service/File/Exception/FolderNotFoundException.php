<?php

namespace Fileknight\Service\File\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class FolderNotFoundException extends ApiException
{
    public function __construct(string $folderId)
    {
        parent::__construct(
            'FOLDER_NOT_FOUND',
            'Required folder was not found',
            Response::HTTP_NOT_FOUND,
            ['folderId' => $folderId]
        );
    }
}
