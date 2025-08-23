<?php

namespace Fileknight\Response;

use Fileknight\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    /**
     * @param array|object $data Data for the response
     * @param string $message Optional message
     * @param int $statusCode HTTP status code
     * @return JsonResponse
     */
    public static function success(
        array|object $data = [],
        string       $message = 'SUCCESS',
        int          $statusCode = 200
    ): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'status' => $statusCode,
        ], $statusCode);
    }

    /**
     * @param string $error The error code
     * @param string $message A more detailed description for the error code
     * @param int $status HTTP status code
     * @param array $details Optional details about the error
     * @return JsonResponse
     */
    public static function error(
        string $error,
        string $message = 'ERROR',
        int    $status = 400,
        array  $details = [],
    ): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $error,
            'message' => $message,
            'details' => $details,
            'status' => $status,
        ], $status);
    }

    public static function fromException(ApiException $exception): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'error' => $exception->getErrorCode(),
            'message' => $exception->getErrorMessage(),
            'details' => $exception->getDetails(),
            'status' => $exception->getStatusCode(),
        ], $exception->getStatusCode());
    }
}
