<?php

namespace Fileknight\Service\User\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class UserNotFoundException extends ApiException
{
    public function __construct(string $username)
    {
        parent::__construct(
            'USER_NOT_FOUND',
            "User $username not found",
            Response::HTTP_NOT_FOUND,
        );
    }
}
