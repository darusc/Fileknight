<?php

namespace Fileknight\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fileknight\Repository\FileRepository;

#[ORM\Entity(repositoryClass: FileRepository::class)]
class File
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Directory::class, inversedBy: 'files')]
    private Directory $directory;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 15)]
    private string $type;

    #[ORM\Column(type: 'integer')]
    private int $size;
}
