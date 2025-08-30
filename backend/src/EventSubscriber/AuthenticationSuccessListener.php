<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Entity\User;
use Fileknight\Response\JWTResponse;
use Fileknight\Service\JWT\JsonWebTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(
    event: 'lexik_jwt_authentication.on_authentication_success',
    method: 'onAuthenticationSuccess'
)]
class AuthenticationSuccessListener
{
    public function __construct(
        private readonly JsonWebTokenService $refreshTokenService
    )
    {
    }

    /**
     * On lexik jwt authentication success, generate a refresh token
     * and modify the response structure
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        $data = $event->getData();

        // Get the generated JWT token and decode its payload
        $jwt = $data['token'];
        $payload = JsonWebTokenService::decode($jwt);

        // Generate a refresh token for the user
        $refreshToken = $this->refreshTokenService->generateNewRefreshToken($user);

        // Create the new response
        $response = JWTResponse::create($jwt, $payload['iat'], $payload['exp'], $refreshToken->getToken());

        // Set the new data
        $event->setData($response);
    }
}
