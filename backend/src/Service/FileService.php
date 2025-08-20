<?php

namespace Fileknight\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\DTO\DirectoryContentDTO;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\DTO\FileDTO;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\UserDirCreationException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Repository\FileRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

class FileService
{
    private string $basepath;
    private FileSystem $filesystem;

    public function __construct(
        private readonly Security               $security,
        private readonly EntityManagerInterface $entityManager,
        private readonly DirectoryRepository    $directoryRepository,
        private readonly FileRepository         $fileRepository,
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

    public function deleteRootDirectory(User $user): void
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

    public function getRootFromDirectory(Directory $directory): Directory
    {
        $current = $directory;
        while ($current->getParent() !== null) {
            $current = $current->getParent();
        }
        return $current;
    }

    /**
     * Get all files and directories in the given directory
     */
    public function getDirectoryContent(Directory $directory): DirectoryContentDTO
    {
        $files = [];
        /** @var File $file */
        foreach ($directory->getFiles() as $file) {
            $files[] = new FileDTO(
                id: $file->getId(),
                name: $file->getName(),
                size: $file->getSize(),
                type: $file->getType(),
            );
        }

        $directories = [];
        /** @var Directory $dir */
        foreach ($directory->getChildren() as $dir) {
            $directories[] = new DirectoryDTO(
                id: $dir->getId(),
                name: $dir->getName(),
            );
        }

        return new DirectoryContentDTO($files, $directories);
    }

    /**
     * Upload a file
     * @param User $user
     * @param Directory $directory The directory where the file should be uploaded to
     * @param UploadedFile $uploadedFile The file to be uploaded
     * @return File The uploaded file
     */
    public function uploadFile(User $user, Directory $directory, UploadedFile $uploadedFile): File
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        $file = new File();
        $file->setName($originalFilename);
        $file->setDirectory($directory);
        $file->setType($uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension());
        $file->setSize($uploadedFile->getSize());

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $uploadedFile->move($this->getRootDirectoryPath($user), $file->getId());

        return $file;
    }

    /**
     * Update file - rename / move
     * @param File $file
     * @param Directory|null $newParentDirectory
     * @param string|null $newName
     * @return void
     */
    public function updateFile(File $file, ?Directory $newParentDirectory, ?string $newName): void
    {
        // Set the new name. The file is not renamed on disk because we use the
        // file's random id as the name on disk
        if ($newName !== null) {
            echo 'update name ' . $newName . PHP_EOL;
            $file->setName($newName);
        }

        // Set new parent directory. As files are stored in a flat system, no
        // file moving on disk is necessary, just updating the database file entry
        if ($newParentDirectory !== null) {
            echo 'update parent directory ' . $newParentDirectory->getName() . PHP_EOL;
            $file->setDirectory($newParentDirectory);
        }

        // Save changes. File is fetched from the database through the repository
        // and already managed by the ORM, so only flushing is necessary to
        // save the changes (no persist needed)
        $this->entityManager->flush();
    }

    /**
     * Delete file
     */
    public function deleteFile(File $file): void
    {
        $this->filesystem->remove($this->getRootDirectoryPath($this->security->getUser()) . '/' . $file->getId());

        $this->entityManager->remove($file);
        $this->entityManager->flush();
    }

    /**
     * Create a new directory. Files are stored in a flat system, directory structure
     * is created only through database records.
     * @param Directory $parentDirectory
     * @param string $name The name of the new directory to be created
     * @return Directory The created directory
     */
    public function createDirectory(Directory $parentDirectory, string $name): Directory
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
    public function updateDirectory(Directory $directory, ?Directory $newParentDirectory, ?string $newName): void
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
    public function deleteDirectory(Directory $directory): void
    {
        foreach ($directory->getChildren() as $childDirectory) {
            $this->deleteDirectory($childDirectory);
        }

        foreach ($directory->getFiles() as $file) {
            $this->deleteFile($file);
        }

        $this->entityManager->remove($directory);
        $this->entityManager->flush();
    }
}
