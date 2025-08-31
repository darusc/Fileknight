<?php

namespace Fileknight\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fileknight\Repository\RefreshTokenRepository;

#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 128)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'refresh_tokens')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $issuedAt;

    #[ORM\Column(type: 'integer')]
    private int $expiresAt;

    #[ORM\Column(type: 'string', length: 256)]
    private string $userAgent;

    #[ORM\Column(type: 'string', length: 256)]
    private string $deviceId;

    #[ORM\Column(type: 'string', length: 256)]
    private string $ip;

    public function isExpired(): bool { return $this->expiresAt <= time(); }

    public function getToken(): string { return $this->token; }
    public function setToken(string $token): void { $this->token = $token; }

    public function getExpiresAt(): int { return $this->expiresAt; }
    public function setExpiresAt(int $expiresAt): void { $this->expiresAt = $expiresAt; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): void { $this->user = $user; }

    public function getDeviceId(): string { return $this->deviceId; }
    public function setDeviceId(string $deviceId): void { $this->deviceId = $deviceId; }

    public function getUserAgent(): string { return $this->userAgent; }
    public function setUserAgent(string $userAgent): void { $this->userAgent = $userAgent; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): void { $this->ip = $ip; }

    public function getIssuedAt(): int { return $this->issuedAt; }
    public function setIssuedAt(int $issuedAt): void { $this->issuedAt = $issuedAt; }
}
