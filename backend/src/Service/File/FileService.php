<?php

namespace Fileknight\Service\File;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\DTO\DirectoryContentDTO;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\DTO\FileDTO;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\FileNotFoundException;
use Fileknight\Repository\FileRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService extends BaseFileService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileRepository         $fileRepository,
    )
    {
        parent::__construct();
    }

    /**
     * @throws FileNotFoundException
     */
    public static function assertFileExists(?File $file): void
    {
        if ($file === null) {
            throw new FileNotFoundException();
        }
    }

    /**
     * Gets the path for the given file is the filesystem.
     * File should be under the given user's root directory.
     */
    public function getFilePath(User $user, File $file): string
    {
        return $this->getRootDirectoryPath($user) . '/' . $file->getId();
    }

    /**
     * Get all files and directories in the given directory
     */
    public function list(Directory $directory): DirectoryContentDTO
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
    public function upload(User $user, Directory $directory, UploadedFile $uploadedFile): File
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
    public function update(File $file, ?Directory $newParentDirectory, ?string $newName): void
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
    public function delete(User $user, File $file): void
    {
        $this->filesystem->remove($this->getRootDirectoryPath($user) . '/' . $file->getId());

        $this->entityManager->remove($file);
        $this->entityManager->flush();
    }
}
