<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Service\User\Exception\UserNotFoundException;
use Fileknight\Service\User\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(
    event: 'lexik_jwt_authentication.on_jwt_decoded',
    method: 'onJWTDecoded'
)]
class JWTDecodedListener
{
    public function __construct(
        private readonly UserService $userService,
    )
    {
    }

    /**
     * This handles JWT invalidation on password changes.
     * If the token was issued before the user was updated in any
     * way it is invalidated
     */
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

        $iat = $payload['iat'] ?? null;
        $username = $payload['username'] ?? null;
        if (!$iat || !$username) {
            $event->markAsInvalid();
            return;
        }

        try {
            $user = $this->userService->getUser($username);
            if($iat < $user->getUpdatedAt()->getTimestamp()) {
                $event->markAsInvalid();
            }
        } catch (UserNotFoundException $_) {
            $event->markAsInvalid();
            return;
        }
    }
}
