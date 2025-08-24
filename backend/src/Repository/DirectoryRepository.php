<?php

namespace Fileknight\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;

class DirectoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Directory::class);
    }

    /**
     * Get the root directory for a given user
     * @throws NonUniqueResultException
     */
    public function findRootByUser(User $user): ?Directory
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.owner = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
