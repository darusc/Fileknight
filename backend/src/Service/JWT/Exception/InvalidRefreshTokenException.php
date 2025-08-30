<?php

namespace Fileknight\Service\JWT\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidRefreshTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct('INVALID_REFRESH_TOKEN', 'Invalid or expired refresh token.', Response::HTTP_UNAUTHORIZED);
    }
}
