<?php

namespace Fileknight\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Fileknight\Entity\User;
use Fileknight\Exception\Auth\InvalidJWTException;
use Fileknight\Service\User\Exception\UserNotFoundException;
use Fileknight\Service\User\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JsonWebTokenSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_DECODED => ['onJWTDecoded'],
            Events::JWT_CREATED => ['onJWTCreated'],
        ];
    }

    public function __construct(
        private readonly UserService $userService,
    )
    {
    }

    /**
     * Adds user id to JWT payload
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['user_id'] = $user->getId();
        $event->setData($payload);
    }

    /**
     * This handles JWT invalidation on password changes.
     * If the token was issued before the user was updated in any
     * way it is invalidated
     * @throws InvalidJWTException
     */
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();

        $iat = $payload['iat'] ?? null;
        $username = $payload['username'] ?? null;
        if (!$iat || !$username) {
            throw new InvalidJWTException();
        }

        try {
            $user = $this->userService->getUser($username);
            if($iat < $user->getUpdatedAt()->getTimestamp()) {
                throw new InvalidJWTException();
            }
        } catch (UserNotFoundException $_) {
            throw new InvalidJWTException();
        }
    }
}
