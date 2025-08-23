<?php

namespace Fileknight\Service\Resolver\Request\Exception;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception is thrown when RequestResolverService doesn't find all the required fields and files in a request object
 */
class IncompleteRequestException extends ApiException
{
    public function __construct(array $missingFields, array $missingFiles)
    {
        $details = [];
        if (count($missingFiles) > 0) {
            $details['files'] = $missingFiles;
        }

        if (count($missingFields) > 0) {
            $details['fields'] = $missingFields;
        }

        parent::__construct(
            'INCOMPLETE_REQUEST',
            "Missing fields or files",
            Response::HTTP_BAD_REQUEST,
            $details
        );
    }
}
