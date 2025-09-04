<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Entity\User;
use Fileknight\Response\JWTResponse;
use Fileknight\Service\JWT\JsonWebTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

#[AsEventListener(
    event: 'lexik_jwt_authentication.on_authentication_success',
    method: 'onAuthenticationSuccess'
)]
class AuthenticationSuccessListener
{
    public function __construct(
        private readonly JsonWebTokenService $refreshTokenService,
        private readonly RequestStack        $requestStack,
        private readonly RateLimiterFactory $loginFailureLimiter,
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

        // Get the user agent and device id headers from the current request
        $request = $this->requestStack->getCurrentRequest();
        $userAgent = $request->headers->get('User-Agent');
        $deviceId = $request->headers->get('Fk-Device-Id');
        $ip = $request->getClientIp() ?? "";

        // Generate a refresh token for the user
        $refreshToken = $this->refreshTokenService->generateNewRefreshToken($user, $userAgent, $deviceId, $ip);

        // Reset the rate limiter after a successful authentication
        $key = $payload['username'] . '_' . $request->getClientIp();
        $this->loginFailureLimiter->create($key)->reset();

        // Set the new response data
        $event->setData(JWTResponse::data($jwt, $payload['iat'], $payload['exp'], $refreshToken->getToken()));
    }
}
