<?php

namespace Fileknight\Service\Resolver\Request\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class UnsupportedContentTypeException extends ApiException
{
    public function __construct(string $contentType)
    {
        parent::__construct(
            'UNSUPPORTED_CONTENT_TYPE',
            "Unsupported content type: $contentType. Only application/json and multipart/form-data are supported.",
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
        );
    }
}
