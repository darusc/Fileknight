<?php

namespace Fileknight\Service\User\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class ExpiredTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'EXPIRED_TOKEN',
            'The provided token is expired',
            Response::HTTP_BAD_REQUEST
        );
    }
}
