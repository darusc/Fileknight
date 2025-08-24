<?php

namespace Fileknight\Exception;

class UserDirCreationException extends \Exception
{
    public function __construct(int $userId, string $error)
    {
        parent::__construct("User dir creation for user $userId failed. Error: $error");
    }
}
