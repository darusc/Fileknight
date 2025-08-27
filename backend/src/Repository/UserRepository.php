<?php

namespace Fileknight\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Fileknight\Entity\User;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    /**
     * Gets all the users who were just created and the account
     * is still pending (the user didn't register yet).
     *
     * Ordered ascending by username
     */
    public function findPendingCreationFinish(): array
    {
        return $this->findBy(['password' => null], ['username' => 'ASC']);
    }

    /**
     * Gets all the users who requested a password reset.
     *
     * Ordered ascending by username
     */
    public function findRequestedReset(): array
    {
        return $this->findBy(['resetRequired' => true], ['username' => 'ASC']);
    }

    /**
     * Gets the remaining users that didn't request a password reset
     * and are not newly created
     */
    public function findNonSpecial(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.password IS NOT NULL')
            ->andWhere('u.resetRequired = false')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getCount(): int
    {
        return count($this->findAll());
    }
}
