<?php

namespace Fileknight\Service\Resolver\Request\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when RequestResolverService can't parse the request body into a valid json object
 */
class InvalidJsonException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct('INVALID_JSON', $message, Response::HTTP_BAD_REQUEST);
    }
}
