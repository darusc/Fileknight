<?php

namespace Fileknight\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fileknight\Repository\FileRepository;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Directory::class, inversedBy: 'files')]
    private Directory $directory;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 15)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $size;

    public function __construct()
    {
        $this->id = str_replace('-', '', Uuid::uuid4()->toString());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }

    public function setDirectory(Directory $directory): void
    {
        $this->directory = $directory;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * Gets the path in the virtual database tree-like structure.
     * Path is relative to the user's root directory.
     *
     * This is not the real path inside the filesystem.
     */
    public function getPath(): string
    {
        return $this->directory->getPath() . '/' . $this->getName();
    }

    /**
     * Gets the physical path (on disk).
     */
    public function getPhysicalPath(): string
    {
        return $this->getId();
    }
}
