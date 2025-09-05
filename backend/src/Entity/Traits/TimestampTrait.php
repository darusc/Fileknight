<?php

namespace Fileknight\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

trait TimestampTrait
{
    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $dt = DateTimeImmutable::createFromFormat('U', (string)time());
        $this->createdAt = $dt;
        $this->updatedAt = $dt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = DateTimeImmutable::createFromFormat('U', (string)time());
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
