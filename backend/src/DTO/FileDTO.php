<?php

namespace Fileknight\DTO;

use Fileknight\Entity\File;
use JsonSerializable;

readonly class FileDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $size,
        public string $type,
    )
    {
    }

    public static function fromEntity(File $file): self
    {
        return new self(
            $file->getId(),
            $file->getName(),
            $file->getSize(),
            $file->getType(),
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
            'size' => $this->size,
            'type' => $this->type,
        ];
    }
}
