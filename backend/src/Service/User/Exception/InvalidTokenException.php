<?php

namespace Fileknight\Service\User\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class InvalidTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'INVALID_TOKEN',
            'The provided token is invalid',
            Response::HTTP_FORBIDDEN
        );
    }
}
