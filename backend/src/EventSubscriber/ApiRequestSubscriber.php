<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Exception\MissingHeaderException;
use Fileknight\Exception\RateLimiterException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Applies a custom header enforcer for /api/login and /api/logout
 * and a rate limiter for /api/login
 */
class ApiRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['customHeaderEnforcer', 100], // Priority 100 to be called before lexik attempts the login
                ['rateLimiter', 90]
            ]
        ];
    }

    public function __construct(
        private RateLimiterFactory $loginFailureLimiter,
        private RateLimiterFactory $passwordChangeLimiter
    )
    {
    }

    /**
     * @throws MissingHeaderException
     */
    public function customHeaderEnforcer(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // POST /api/login
        // Enforce a custom header (Fk-Device-Id) on the login request
        if ($request->getPathInfo() === '/api/auth/login' && $request->getMethod() === 'POST') {
            if (!$request->headers->has('Fk-Device-Id')) {
                throw new MissingHeaderException('Fk-Device-Id');
            }
        }

        // POST /api/logout
        // Enforce a custom header (Fk-Device-Id) on the logout request
        if ($request->getPathInfo() === '/api/auth/logout' && $request->getMethod() === 'POST') {
            if (!$request->headers->has('Fk-Device-Id')) {
                throw new MissingHeaderException('Fk-Device-Id');
            }
        }
    }

    /**
     * @throws RateLimiterException
     */
    public function rateLimiter(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $for = "";
        $limiter = null;

        // Handle rate limiting for login endpoint
        if ($request->getPathInfo() === '/api/auth/login' && $request->getMethod() === 'POST') {
            $username = json_decode($request->getContent(), true)['username'] ?? null;
            $key = ($username ?? '_') . '_' . $request->getClientIp();
            $limiter = $this->loginFailureLimiter->create($key);
            $for = 'LOGIN';
        }

        // Handle rate limiting for password editing
        $matches = [];
        if (preg_match('#^/api/auth/\d+/edit/password$#', $request->getPathInfo(), $matches) && $request->getMethod() === 'PATCH') {
            $key = $matches[1] . '_' . $request->getClientIp();
            $limiter = $this->passwordChangeLimiter->create($key);
            $for = 'EDIT_PASSWORD';
        }

        if ($limiter && !($limit = $limiter->consume(1))->isAccepted()) {
            throw new RateLimiterException($for, $limit->getRetryAfter()->getTimestamp());
        }
    }
}
