<?php

namespace Fileknight\Exception;

class DirectoryNotFoundException extends \Exception
{
    public function __construct(?string $directory = null)
    {
        $dir = $directory ?? '';
        parent::__construct("Directory $dir not found.");
    }
}
