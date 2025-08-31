<?php

namespace Fileknight\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100] // Priority 100 to be called before lexik attempts the login
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // POST /api/login
        // Enforce a custom header (Fk-Device-Id) on the login request
        if ($request->getPathInfo() === '/api/auth/login' && $request->getMethod() === 'POST') {
            if (!$request->headers->has('Fk-Device-Id')) {
                throw new BadRequestHttpException('Missing Fk-Device-Id header');
            }
        }

        // POST /api/logout
        // Enforce a custom header (Fk-Device-Id) on the logout request
        if ($request->getPathInfo() === '/api/auth/logout' && $request->getMethod() === 'POST') {
            if (!$request->headers->has('Fk-Device-Id')) {
                throw new BadRequestHttpException('Missing Fk-Device-Id header');
            }
        }
    }
}
