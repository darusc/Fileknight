<?php

namespace Fileknight\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Exception\UserAlreadyExists;
use Fileknight\Exception\UserDirCreationException;
use Fileknight\Repository\UserRepository;
use Fileknight\Service\File\DirectoryService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service the handles user registration and deletion
 */
readonly class UserService
{
    public function __construct(
        private EntityManagerInterface      $entityManager,
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private DirectoryService            $directoryService,
    )
    {
    }

    /**
     * Creates a new user with the given email and password.
     * @return User The newly created user
     * @throws UserAlreadyExists
     * @throws UserDirCreationException
     */
    public function register(string $username, string $password): User
    {
        if ($this->userRepository->findOneBy(['username' => $username])) {
            throw new UserAlreadyExists($username);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        try {
            $this->directoryService->createRoot($user);
        } catch (UserDirCreationException $exception) {
            // Delete the user if root directory creation failed
            // and rethrow the exception
            $this->delete($user);
            throw $exception;
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $this->directoryService->deleteRoot($user);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
