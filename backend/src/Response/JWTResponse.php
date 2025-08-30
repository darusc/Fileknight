<?php

namespace Fileknight\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class JWTResponse
{
    public static function create(string $jwt, string $iat, string $exp, string $refreshToken): array
    {
        return [
            'jwt' => $jwt,
            'iat' => $iat,
            'exp' => $exp,
            'refresh_token' => $refreshToken,
        ];
    }

    public static function json(string $jwt, string $iat, string $exp, string $refreshToken): JsonResponse
    {
        return new JsonResponse(self::create($jwt, $iat, $exp, $refreshToken));
    }
}
