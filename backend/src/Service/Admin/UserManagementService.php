<?php

namespace Fileknight\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Service\Admin\Exception\UserCreationFailedException;
use Fileknight\Service\File\DirectoryService;
use Ramsey\Uuid\Generator\RandomBytesGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Admin service for managing users
 */
readonly class UserManagementService
{
    /**
     * @var int Lifetime for password reset tokens
     */
    private int $resetTokenLifetime;

    /**
     * @var int Lifetime for new user create token
     */
    private int $createTokenLifetime;

    public function __construct(
        #[Autowire('%env(int:RESET_TOKEN_LIFETIME)%')] int  $resetTokenLifetime,
        #[Autowire('%env(int:CREATE_TOKEN_LIFETIME)%')] int $createTokenLifetime,
        private EntityManagerInterface                      $entityManager,
        private DirectoryService                            $directoryService,
    )
    {
        $this->resetTokenLifetime = $resetTokenLifetime;
        $this->createTokenLifetime = $createTokenLifetime;
    }

    public static function generateSecureToken(int $length = 32): string
    {
        $randomGenerator = new RandomBytesGenerator();
        return bin2hex($randomGenerator->generate($length));
    }

    /**
     * Creates a new user with the given username. The user's root directory
     * is also created now.
     *
     * @param string $username
     * @return array Contains [0] the token the user needs for final registration (setting his password) and [1] its lifetime
     * @throws UserCreationFailedException
     */
    public function create(string $username): array
    {
        if ($this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]) !== null) {
            throw new UserCreationFailedException("User $username already exists.");
        }

        // Create a new user and assign a new secure token
        // that will be used for finishing the registration
        // process (i.e. the user will set the password)
        $user = new User();

        $token = self::generateSecureToken();
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        if (!$hashedToken) {
            throw new UserCreationFailedException("Error hashing the registration token.");
        }

        $user->setUsername($username);
        $user->setResetToken($hashedToken, $this->createTokenLifetime);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        try {
            $this->directoryService->createRoot($user);
        } catch (IOException $exception) {
            // Delete the user if root directory creation failed and rethrow the exception
            $this->delete($user);
            throw new UserCreationFailedException($exception->getMessage());
        }

        return [$token, $this->createTokenLifetime];
    }

    public function delete(User $user): void
    {
        $this->directoryService->deleteRoot($user);
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
