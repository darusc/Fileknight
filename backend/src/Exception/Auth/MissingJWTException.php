<?php

namespace Fileknight\Exception\Auth;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class MissingJWTException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'MISSING_JWT',
            'Missing JWT token',
            Response::HTTP_UNAUTHORIZED,
        );
    }
}
