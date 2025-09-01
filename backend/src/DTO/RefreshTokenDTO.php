<?php

namespace Fileknight\DTO;

use Fileknight\Entity\RefreshToken;

class RefreshTokenDTO
{
    public function __construct(
        public string $token,
        public int    $issuedAt,
        public string $userAgent,
        public string $deviceId,
        public string $ip,
    )
    {
    }

    public static function fromEntity(RefreshToken $token): self
    {
        return new self(
            $token->getToken(),
            $token->getIssuedAt(),
            $token->getUserAgent(),
            $token->getDeviceId(),
            $token->getIp()
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'issued_at' => $this->issuedAt,
            'userAgent' => $this->userAgent,
            'deviceId' => $this->deviceId,
            'ip' => $this->ip
        ];
    }
}
