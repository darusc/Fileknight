<?php

namespace Fileknight\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

trait DeletedTrait
{
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}
