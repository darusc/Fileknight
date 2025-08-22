<?php

namespace Fileknight\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fileknight\Entity\Traits\TimestampTrait;
use Fileknight\Repository\DirectoryRepository;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: DirectoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Directory
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 32, unique: true)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'directory', targetEntity: File::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Directory::class, inversedBy: 'children')]
    private ?Directory $parent = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $owner = null;

    public function __construct()
    {
        $this->id = str_replace('-', '', Uuid::uuid4()->toString());
        $this->files = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): void
    {
        $this->files->add($file);
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addDirectory(Directory $directory): void
    {
        $this->children->add($directory);
    }

    public function getParent(): ?Directory
    {
        return $this->parent;
    }

    /**
     * If owner is not null this directory is the root directory and won't allow any parent
     * @param Directory $parent
     * @return void
     */
    public function setParent(Directory $parent): void
    {
        if ($this->owner === null) {
            $this->parent = $parent;
        }
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Owner should be null for non-root directories.
     * @param User $owner
     * @return void
     */
    public function setOwner(User $owner): void
    {
        if ($this->parent === null) {
            $this->owner = $owner;
        }
    }

    /**
     * Gets the path in the virtual database tree-like structure.
     * Path is relative to the user's root directory.
     *
     * This is not the real path inside the filesystem (There are
     * no directories inside the filesystem, only the user's root
     * directory)
     */
    public function getPath(): string
    {
        $path = [$this->name];
        $current = $this->parent;
        while ($current !== null) {
            $path[] = $current->getName();
            $current = $current->getParent();
        }

        return implode('/', array_reverse($path));
    }

    /**
     * Get the root. Root directory is the user directory on disk
     */
    public function getRoot(): Directory
    {
        $current = $this;
        while ($current->getParent() !== null) {
            $current = $current->getParent();
        }
        return $current;
    }
}
