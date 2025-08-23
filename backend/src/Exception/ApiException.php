<?php

namespace Fileknight\Exception;

use Exception;

class ApiException extends Exception
{
    /**
     * @param string $errorCode String representing the error code: (e.g. FILE_NOT_FOUND)
     * @param string $errorMessage String representing a custom message to go along with the error code
     * @param int $statusCode Http status code corresponding to the error
     * @param array $details Optional details about the error
     */
    public function __construct(
        private readonly string $errorCode,
        private readonly string $errorMessage,
        private readonly int $statusCode,
        private array $details = []
    )
    {
        parent::__construct($this->errorMessage, $this->statusCode);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
