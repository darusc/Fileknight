<?php

namespace Fileknight\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Exception\UserAlreadyExists;
use Fileknight\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service the handles user registration and deletion
 */
readonly class UserService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    /**
     * Creates a new user with the given email and password.
     * @return User The newly created user
     * @throws UserAlreadyExists
     */
    public function register(string $email, string $password): User
    {
        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new UserAlreadyExists($email);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
