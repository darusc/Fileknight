<?php

namespace Fileknight\Exception;

use Fileknight\Entity\File;

class FileAccessDeniedException extends \Exception
{
    public function __construct(File $file)
    {
        parent::__construct("Access denied for file '{$file->getName()}'.");
    }
}
