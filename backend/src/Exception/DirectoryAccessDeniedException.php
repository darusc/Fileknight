<?php

namespace Fileknight\Exception;

class DirectoryAccessDeniedException extends \Exception
{
    public function __construct(string $folderId)
    {
        parent::__construct("Access denied for directory '$folderId'.");
    }
}
