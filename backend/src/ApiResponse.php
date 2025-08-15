<?php

namespace Fileknight;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(
        array|object $data = [],
        string $message = 'success',
        int $statusCode = 200): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'code' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(
        array $errors = [],
        string $message = 'error',
        int $statusCode = 400
    ): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
