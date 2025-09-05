<?php

namespace Fileknight\DTO;

use Fileknight\Entity\Directory;
use JsonSerializable;

readonly class DirectoryDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public int    $createdAt,
        public int    $updatedAt,
    )
    {
    }

    public static function fromEntity(Directory $directory): self
    {
        return new self(
            $directory->getId(),
            $directory->getName(),
            $directory->getCreatedAt()->getTimestamp(),
            $directory->getUpdatedAt()->getTimestamp()
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
            'name' => $this->name,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
