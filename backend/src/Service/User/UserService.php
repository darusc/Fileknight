<?php

namespace Fileknight\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Exception\ApiException;
use Fileknight\Repository\UserRepository;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\User\Exception\ExpiredTokenException;
use Fileknight\Service\User\Exception\InvalidTokenException;
use Fileknight\Service\User\Exception\UserNotFoundException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
     * @throws UserNotFoundException
     */
    public function getUser(string $username): User
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if(!$user) {
            throw new UserNotFoundException($username);
        }
        return $user;
    }

    /**
     * Finish the user creation process. Entity was created by admin,
     * now finish the process (set the password)
     *
     * @param User $user The user to register
     * @param string $password User's password
     * @param string $token The registration token
     * @throws ApiException
     */
    public function register(User $user, string $password, string $token): void
    {
        // Verify token validity and expiration time
        if (!password_verify($token, $user->getResetToken())) {
            throw new InvalidTokenException();
        }
        if ($user->getResetTokenExp() < time()) {
            throw new ExpiredTokenException();
        }

        // Finally set the password and update the entity in database
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->flush();
    }
}
