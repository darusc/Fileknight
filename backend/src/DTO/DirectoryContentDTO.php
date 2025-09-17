<?php

namespace Fileknight\DTO;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * DTO class that represents the contents of a directory.
 * Contains two collections: 1 for the contained files
 * and 1 for the contained directories
 */
readonly class DirectoryContentDTO implements JsonSerializable
{

    /**
     * @param FileDTO[] $files
     * @param DirectoryDTO[] $directories
     */
    public function __construct(
        public string $id,
        public string $name,
        public array  $files,
        public array  $directories,
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
            'directories' => array_map(function (DirectoryDTO $dto) {
                return $dto->toArray();
            }, $this->directories),
            'files' => array_map(function (FileDTO $dto) {
                return $dto->toArray();
            }, $this->files)
        ];
    }
}
