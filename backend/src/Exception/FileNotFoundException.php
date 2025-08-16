<?php

namespace Fileknight\Exception;

class FileNotFoundException extends \Exception
{
    public function __construct(string $file)
    {
        parent::__construct("File not found: $file");
    }
}
