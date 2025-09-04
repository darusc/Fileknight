<?php

namespace Fileknight\Exception\Auth;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidJWTException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'INVALID_JWT',
            'Invalid or expired JWT token',
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
