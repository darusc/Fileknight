<?php

namespace Fileknight\Exception\Auth;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class MissingHeaderException extends ApiException
{
    public function __construct(string $header)
    {
        parent::__construct(
            'MISSING_HEADER',
            "Missing header $header",
            Response::HTTP_BAD_REQUEST
        );
    }
}
