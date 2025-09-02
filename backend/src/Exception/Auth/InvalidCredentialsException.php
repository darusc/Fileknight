<?php

namespace Fileknight\Exception\Auth;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidCredentialsException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'INVALID_CREDENTIALS',
            'Provided credentials are invalid.',
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
