<?php

namespace Fileknight\Service\File;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\UserDirCreationException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class DirectoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DirectoryRepository    $directoryRepository,
        private FileService            $fileService,
        private FileSystem             $filesystem
    )
    {
    }

    /**
     * Gets the real physical path (on disk) to the user's root directory
     * from the given UserInterface by its identifier
     */
    public static function getRootDirectoryPathFromUser(UserInterface $user): string
    {
        return $_ENV['USER_STORAGE_PATH'] . '/' . $user->getUserIdentifier();
    }

    /**
     * Gets the real physical path (on disk) of the root directory
     * from the given directory
     */
    public static function getRootDirectoryPathFromDir(Directory $directory): string
    {
        return $_ENV['USER_STORAGE_PATH'] . '/' . $directory->getRoot()->getName();
    }

    /**
     * Get the directory with the given id.
     * @param string $id
     * @return Directory
     * @throws FolderNotFoundException
     */
    public function get(string $id): Directory
    {
        $directory = $this->directoryRepository->find($id);
        if (!$directory) {
            throw new FolderNotFoundException($id);
        }

        return $directory;
    }

    /**
     * Creates a new root user directory env('USER_STORAGE_PATH')/{userId} if it doesn't already exist.
     * If it already exists do nothing
     * @throws UserDirCreationException
     */
    public function createRoot(User $user): void
    {
        if (!$this->rootDirectoryExists($user)) {
            $path = static::getRootDirectoryPathFromUser($user);
            try {
                // Create the physical directory
                $this->filesystem->mkdir($path, 0775);

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

    public function deleteRoot(User $user): void
    {
        if ($this->rootDirectoryExists($user)) {
            // Remove directory database mapping
            $directory = $this->directoryRepository->findOneBy(['owner' => $user]);
            $this->entityManager->remove($directory);
            $this->entityManager->flush();

            // Remove the directory from physical storage
            $this->filesystem->remove(static::getRootDirectoryPathFromUser($user));
        }
    }

    /**
     * Create a new directory. Files are stored in a flat system, directory structure
     * is created only through database records.
     * @param Directory $parentDirectory
     * @param string $name The name of the new directory to be created
     * @return Directory The created directory
     */
    public function create(Directory $parentDirectory, string $name): Directory
    {
        $directory = new Directory();
        $directory->setName($name);
        $directory->setParent($parentDirectory);

        $this->entityManager->persist($directory);
        $this->entityManager->flush();

        return $directory;
    }

    /**
     * Update directory - rename / move
     * @param Directory $directory
     * @param Directory|null $newParentDirectory
     * @param string|null $newName
     * @return void
     */
    public function update(Directory $directory, ?Directory $newParentDirectory, ?string $newName): void
    {
        // As the directory structure is purely virtual no disk
        // operations are required (no renaming / moving)

        // Set new name.
        if ($newName !== null) {
            $directory->setName($newName);
        }

        // Set new parent directory.
        if ($newParentDirectory !== null) {
            $directory->setParent($newParentDirectory);
        }

        // Save changes. Directory is fetched from the database through the repository
        // and already managed by the ORM, so only flushing is necessary to
        // save the changes (no persist needed)
        $this->entityManager->flush();
    }

    /**
     * Recursively delete a directory
     * @param Directory $directory
     * @return void
     */
    public function delete(Directory $directory): void
    {
        foreach ($directory->getChildren() as $childDirectory) {
            $this->delete($childDirectory);
        }

        foreach ($directory->getFiles() as $file) {
            $this->fileService->delete($file);
        }

        $this->entityManager->remove($directory);
        $this->entityManager->flush();
    }

    private function rootDirectoryExists(UserInterface $user): bool
    {
        $path = static::getRootDirectoryPathFromUser($user);
        return $this->filesystem->exists($path);
    }
}
