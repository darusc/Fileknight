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
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

class FileService
{
    private string $basepath;
    private FileSystem $filesystem;

    public function __construct(
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
     * @return void
     */
    public function uploadFile(User $user, Directory $directory, UploadedFile $uploadedFile): void
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

        // If there are other files with same name add a count
        // to its name like example (2).txt if example.txt already exists
        $index = $this->fileRepository->findNextFilenameIndex($directory, $originalFilename);
        if ($index++ > 0) {
            $originalFilename .= " ($index)";
        }

        $file = new File();
        $file->setName($originalFilename);
        $file->setDirectory($directory);
        $file->setType($uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension());
        $file->setSize($uploadedFile->getSize());

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $uploadedFile->move($this->getRootDirectoryPath($user), $file->getId());
    }
}
