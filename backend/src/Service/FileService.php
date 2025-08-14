<?php

namespace Fileknight\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;
use Fileknight\Exception\UserDirCreationException;
use Fileknight\Repository\UserRepository;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;

class FileService
{
    private string $basepath;
    private FileSystem $filesystem;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
        $this->basepath = $_ENV['USER_STORAGE_PATH'];
        $this->filesystem = new Filesystem();
    }

    public function getRootDirectoryPath(UserInterface $user): string
    {
        return $this->basepath . '/' . $user->getUserIdentifier();
    }

    public function rootDirectoryExists(UserInterface $user): bool
    {
        $path = $this->getRootDirectoryPath($user);
        return $this->filesystem->exists($path);
    }

    /**
     * Creates a new root user directory env('USER_STORAGE_PATH')/{userId} if it doesn't already exist.
     * If it already exists do nothing
     * @throws UserDirCreationException
     */
    public function createRootDirectory(User $user): void
    {
        if(!$this->rootDirectoryExists($user)) {
            $path = $this->getRootDirectoryPath($user);
            try {
                // Create the physical directory
                $this->filesystem->mkdir($path, 0666);

                // Create the database entry mapping the physical directory
                $rootDirectory = new Directory();
                $rootDirectory->setName($user->getUserIdentifier());
                $rootDirectory->setOwner($user);

                $this->entityManager->persist($rootDirectory);
                $this->entityManager->flush();

            } catch (IOException $e) {
                throw new UserDirCreationException($user->getUserIdentifier(), $e->getMessage());
            }
        }
    }

    public function deleteRootDirectory(User $user): void
    {
        if($this->rootDirectoryExists($user)) {
            // Remove directory database mapping
            $directory = $this->entityManager->getRepository(Directory::class)->findOneBy(['owner' => $user]);
            $this->entityManager->remove($directory);
            $this->entityManager->flush();

            // Remove the directory from physical storage
            $this->filesystem->remove($this->getRootDirectoryPath($user));
        }
    }

    public function uploadFile(UserInterface $user, File $file): void
    {

    }
}
