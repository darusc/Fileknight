<?php

namespace Fileknight\DTO;

use DateTimeImmutable;
use Fileknight\Entity\User;
use JsonSerializable;

readonly class UserDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $username,
        public array  $roles,
        public int $createdAt,
    )
    {
    }

    public static function fromEntity(User $user): static
    {
        return new static(
            $user->getId(),
            $user->getUsername(),
            $user->getRoles(),
            $user->getCreatedAt()->getTimestamp(),
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'roles' => $this->roles,
            'createdAt' => $this->createdAt,
        ];
    }
}
