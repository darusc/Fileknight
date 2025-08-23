<?php

namespace Fileknight\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class RequestDataDTO
{
    private array $fields;

    public function add(string $name, mixed $value): void
    {
        $this->fields[$name] = $value;
    }

    /**
     * Get the field with the given name. If it exists return its value,
     * if it doesn't exist return null.
     *
     * If an optional fields is given a null value use exists() before using.
     * This function also returns null when the field doesn't exist
     */
    public function get(string $name): mixed
    {
        return $this->fields[$name] ?? null;
    }

    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }
}
