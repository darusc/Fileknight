<?php

namespace Fileknight\Exception;

class DirectoryNotFoundException extends \Exception
{
    public function __construct(string $directory)
    {
        parent::__construct("Directory '$directory' not found.");
    }
}
