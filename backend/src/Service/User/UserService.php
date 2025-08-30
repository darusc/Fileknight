<?php

namespace Fileknight\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Exception\ApiException;
use Fileknight\Exception\ForbiddenException;
use Fileknight\Repository\UserRepository;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\JWT\JsonWebTokenService;
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
        private JsonWebTokenService         $jwtService,
    )
    {
    }

    /**
     * @throws UserNotFoundException
     */
    public function getUser(string $username): User
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            throw new UserNotFoundException($username);
        }
        return $user;
    }

    /**
     * @throws UserNotFoundException
     */
    public function getUserById(int $userId): User
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new UserNotFoundException($userId);
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

        // Invalidate the current token so it cannot be used again
        $user->invalidateToken();
        // Remove all refresh tokens for this user, as this endpoint
        // is used for resetting the password as well
        $this->jwtService->invalidateAllRefreshTokens($user);

        // Finally set the password and update the entity in database
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->flush();
    }

    /**
     * Request a password reset for the given user.
     */
    public function requestReset(User $user): void
    {
        $user->setResetRequired(true);
        $this->entityManager->flush();
    }

    /**
     * Change user's password.
     * @throws ForbiddenException
     */
    public function changePassword(User $user, string $old, string $new): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $old)) {
            throw new ForbiddenException('Old password is invalid');
        }

        // Remove all refresh tokens for this user
        $this->jwtService->invalidateAllRefreshTokens($user);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $new);
        $user->setPassword($hashedPassword);

        $this->entityManager->flush();
    }
}
