<?php

namespace Fileknight\Exception;

use Symfony\Component\HttpFoundation\Response;

class RateLimiterException extends ApiException
{
    public function __construct(string $for, string $retryAfter)
    {
        parent::__construct(
            'LIMIT_EXCEEDED',
            "Rate limit for $for exceeded",
            Response::HTTP_TOO_MANY_REQUESTS,
            ['retryAfter' => $retryAfter],
            ['Retry-After' => $retryAfter]
        );
    }
}
