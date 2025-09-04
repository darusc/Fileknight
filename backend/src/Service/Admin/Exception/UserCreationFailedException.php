<?php

namespace Fileknight\Service\Admin\Exception;

use Exception;

class UserCreationFailedException extends Exception
{
    public function __construct(private readonly string $reason)
    {
        parent::__construct("User creation failed: $reason");
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
