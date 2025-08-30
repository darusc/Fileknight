<?php

namespace Fileknight\Service\JWT;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\RefreshToken;
use Fileknight\Entity\User;
use Fileknight\Service\Admin\UserManagementService;
use Fileknight\Service\JWT\Exception\InvalidRefreshTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class JsonWebTokenService
{
    public function __construct(
        #[Autowire('%env(int:REFRESH_TOKEN_LIFETIME)%')]
        private int                      $refreshTokenLifetime,
        private EntityManagerInterface   $entityManager,
        private JWTTokenManagerInterface $jwtTokenManager,
    )
    {
    }

    /**
     * Decodes a JWT token payload
     * @return array|null The payload or null if it is invalid
     */
    public static function decode(string $jwt): array|null
    {
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            return null;
        }

        $payload = base64_decode(strtr($parts[1], '-_.', '+/='));
        if (!$payload) {
            return null;
        }

        return json_decode($payload, true);
    }

    /**
     * Generates a new refresh token for the given user
     */
    public function generateNewRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setToken(UserManagementService::generateSecureToken(64));
        $refreshToken->setExpiresAt(time() + $this->refreshTokenLifetime);

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    /**
     * Use the refresh token to generate a new JWT token. (The used refresh token is invalidated)
     * @return array Containing the newly generated jwt [0] and the refresh token [1]
     * @throws InvalidRefreshTokenException
     */
    public function refresh(string $refreshToken): array
    {
        $token = $this->getRefreshToken($refreshToken);

        // Get the associated user with the refresh token and generate a new one
        $user = $token->getUser();
        $newRefreshToken = $this->generateNewRefreshToken($user);

        // Delete the old refresh token, and store the new one
        $this->entityManager->remove($token);
        $this->entityManager->persist($newRefreshToken);
        $this->entityManager->flush();

        return [$this->generateNewJWT($user), $this->generateNewRefreshToken($user)->getToken()];
    }

    /**
     * Get the refresh token from repository and validate it
     * @throws InvalidRefreshTokenException
     */
    private function getRefreshToken(string $token): RefreshToken
    {
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->find($token);
        if (!$refreshToken || $refreshToken->isExpired()) {
            throw new InvalidRefreshTokenException();
        }

        return $refreshToken;
    }

    /**
     * Creates a new JWT using lexik's JWT Authentication Bundle
     */
    private function generateNewJWT(User $user): string
    {
        return $this->jwtTokenManager->create($user);
    }
}
