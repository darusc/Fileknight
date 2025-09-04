<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Exception\Auth\InvalidCredentialsException;
use Fileknight\Exception\Auth\InvalidJWTException;
use Fileknight\Exception\Auth\MissingJWTException;
use Fileknight\Response\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class that listens to lexik {@see AuthenticationFailureEvent} and
 * wraps it in a {@see ApiResponse} by throwing an {@see InvalidCredentialsException}
 */
class AuthenticationFailedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            Events::JWT_INVALID => 'onJwtInvalid',
            Events::JWT_EXPIRED => 'onJwtInvalid',
            Events::JWT_NOT_FOUND => 'onJwtNotFound',
        ];
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        throw new InvalidCredentialsException();
    }

    /**
     * @throws InvalidJWTException
     */
    public function onJwtInvalid(AuthenticationFailureEvent $event): void
    {
        throw new InvalidJWTException();
    }

    /**
     * @throws MissingJWTException
     */
    public function onJwtNotFound(AuthenticationFailureEvent $event): void
    {
        throw new MissingJWTException();
    }
}
