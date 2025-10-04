<?php

namespace Fileknight\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;

/**
 * @extends ServiceEntityRepository<Directory>
 */
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

    /**
     * @return Directory[]
     */
    public function findAllBinned(): array {
        $query = $this->createQueryBuilder('d')
            ->select('*')
            ->where('d.deletedAt IS NOT NULL')
            ->addOrderBy('d.deletedAt', 'DESC')
            ->addOrderBy('d.name', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
