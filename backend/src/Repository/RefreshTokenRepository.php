<?php

namespace Fileknight\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Fileknight\Entity\RefreshToken;
use Fileknight\Entity\User;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, RefreshToken::class);
    }

    /**
     * Delete all refresh tokens for the given user
     */
    public function deleteAllByUser(User $user): void
    {
        $query = $this->createQueryBuilder('r')
            ->delete(RefreshToken::class, 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        $query->execute();
    }

    /**
     * Delete all refresh tokens for the given user
     * corresponding to the given device
     */
    public function deleteAllByDevice(User $user, string $deviceId): void
    {
        $query = $this->createQueryBuilder('r')
            ->delete(RefreshToken::class, 'r')
            ->where('r.user = :user')
            ->andWhere('r.deviceId = :deviceId')
            ->setParameter('user', $user)
            ->setParameter('deviceId', $deviceId)
            ->getQuery();

        $query->execute();
    }
}
