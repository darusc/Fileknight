<?php

namespace Fileknight\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fileknight\Entity\Traits\TimestampTrait;
use Fileknight\Repository\FileRepository;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class File
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Directory::class, inversedBy: 'files')]
    private Directory $directory;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 15)]
    private string $extension;

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

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
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
     * Path is absolute starting at the user's root directory. (e.g. /grandparent/parent/file.txt)
     *
     * This is not the real path inside the filesystem.
     */
    public function getPath(): string
    {
        return $this->directory->getPath() . '/' . $this->getName();
    }

    /**
     * Gets the path in the virtual database tree-like structure.
     * Path is relative to the given ascendant.
     * e.g. /f1/f2/f3/file.txt. If ascendant is f2 path will be f2/f3/file.txt
     */
    public function getPathFromAscendant(Directory $ascendant): string
    {
        echo 'get file path for file ' . $this->name . ' from ascendant ' . $ascendant->getName() . PHP_EOL;
        return $this->directory->getPathFromAscendant($ascendant) . '/' . $this->getName();
    }
}
