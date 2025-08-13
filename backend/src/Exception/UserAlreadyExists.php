<?php

namespace Fileknight\Exception;

class UserAlreadyExists extends \Exception
{
    public function __construct(string $email)
    {
        parent::__construct("User $email already exists", 409);
    }
}
