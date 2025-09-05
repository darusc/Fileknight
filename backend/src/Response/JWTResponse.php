<?php

namespace Fileknight\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JWTResponse
{
    public static function data(string $jwt, string $iat, string $exp, string $refreshToken): array
    {
        return json_decode(self::json($jwt, $iat, $exp, $refreshToken)->getContent(), true);
    }

    public static function json(string $jwt, string $iat, string $exp, string $refreshToken): JsonResponse
    {
        return ApiResponse::success(
            [
                'jwt' => $jwt,
                'iat' => $iat,
                'exp' => $exp,
                'refresh_token' => $refreshToken
            ],
            'Authentication successful with JWT token'
        );
    }
}
