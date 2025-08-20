<?php

namespace Fileknight\Service\File;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Exception\UserDirCreationException;
use Fileknight\Repository\DirectoryRepository;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Security\Core\User\UserInterface;

class DirectoryService extends BaseFileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DirectoryRepository    $directoryRepository,
        private readonly FileService            $fileService,
    )
    {
        parent::__construct();
    }

    /**
     * @throws DirectoryNotFoundException
     */
    public static function assertDirectoryExists(?Directory $directory): void
    {
        if ($directory === null) {
            throw new DirectoryNotFoundException();
        }
    }

    public static function getRootFromDirectory(Directory $directory): Directory
    {
        $current = $directory;
        while ($current->getParent() !== null) {
            $current = $current->getParent();
        }
        return $current;
    }

    /**
     * Creates a new root user directory env('USER_STORAGE_PATH')/{userId} if it doesn't already exist.
     * If it already exists do nothing
     * @throws UserDirCreationException
     */
    public function createRoot(User $user): void
    {
        if (!$this->rootDirectoryExists($user)) {
            $path = $this->getRootDirectoryPath($user);
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
            $this->filesystem->remove($this->getRootDirectoryPath($user));
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
        $path = $this->getRootDirectoryPath($user);
        return $this->filesystem->exists($path);
    }
}
