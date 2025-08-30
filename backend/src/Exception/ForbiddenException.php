<?php

namespace Fileknight\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ForbiddenException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct('ACTION_FORBIDDEN', $message, Response::HTTP_FORBIDDEN);
    }
}
