<?php

namespace Fileknight\DTO;

use Fileknight\Entity\Directory;
use JsonSerializable;

readonly class DirectoryDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
    )
    {
    }

    public static function fromEntity(Directory $directory): self
    {
        return new self(
            $directory->getId(),
            $directory->getName(),
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
        ];
    }
}
