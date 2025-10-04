<?php

namespace Fileknight\DTO;

use Fileknight\Entity\File;
use JsonSerializable;

readonly class FileDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
        public int    $size,
        public string $mimeType,
        public string $extension,
        public int    $createdAt,
        public int    $updatedAt,
        public ?int    $deletedAt,
    )
    {
    }

    public static function fromEntity(File $file): self
    {
        return new self(
            $file->getId(),
            $file->getName(),
            $file->getSize(),
            $file->getMimeType(),
            $file->getExtension(),
            $file->getCreatedAt()->getTimestamp(),
            $file->getUpdatedAt()->getTimestamp(),
            $file->getDeletedAt()?->getTimestamp(),
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
            'mimeType' => $this->mimeType ?? "",
            'extension' => $this->extension,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
