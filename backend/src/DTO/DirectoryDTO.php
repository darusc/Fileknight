<?php

namespace Fileknight\DTO;

use JsonSerializable;

readonly class DirectoryDTO implements JsonSerializable
{
    public function __construct(
        public string $id,
        public string $name,
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
        ];
    }
}
