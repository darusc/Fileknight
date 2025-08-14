<?php

namespace Fileknight\DTO;

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
