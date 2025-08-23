<?php

namespace Fileknight\EventSubscriber;

use Fileknight\Exception\ApiException;
use Fileknight\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ApiException) {
            // Handle custom exceptions that extend ApiException
            $response = ApiResponse::fromException($exception);
        } elseif ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() === Response::HTTP_REQUEST_ENTITY_TOO_LARGE) {
            // Handle symfony exception thrown when the request size is too large
            $response = ApiResponse::error(
                'REQUEST_ENTITY_TOO_LARGE',
                'The uploaded file or request body is too large',
                $exception->getStatusCode(),
            );
        } else {
            // Handle any other exception
            $response = ApiResponse::error(
                'INTERNAL_SERVER_ERROR',
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['code' => $exception->getCode()]
            );
        }

        $event->setResponse($response);
    }
}
