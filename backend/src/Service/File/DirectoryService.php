<?php

namespace Fileknight\Service\File;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Fileknight\DTO\FileDTO;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class DirectoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DirectoryRepository    $directoryRepository,
        private FileSystem             $filesystem,
        private FileService            $fileService,
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
     * @throws IOException
     */
    public function createRoot(User $user): void
    {
        if (!$this->rootDirectoryExists($user)) {
            $path = static::getRootDirectoryPathFromUser($user);
            // Create the physical directory
            $this->filesystem->mkdir($path, 0775);

            // Create the database entry mapping the physical directory
            $rootDirectory = new Directory();
            $rootDirectory->setName($user->getUserIdentifier());
            $rootDirectory->setOwner($user);

            $this->entityManager->persist($rootDirectory);
            $this->entityManager->flush();
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
     * Mark the directory as binned
     * @param Directory $directory
     * @return void
     */
    public function delete(Directory $directory): void
    {
        $directory->setDeletedAt(DateTimeImmutable::createFromFormat('U', (string)time()));
        $this->entityManager->flush();
    }

    /**
     * Restore directory from bin
     */
    public function restore(Directory $directory): void
    {
        $directory->setDeletedAt(null);
        $this->entityManager->flush();
    }

    /**
     * Recursively delete a folder and its children from the filesystem
     * and all associated database entries
     */
    public function hardDelete(Directory $directory): void
    {
        // Fail if the directory was not added to bin before
        if($directory->getDeletedAt() === null) {
            return;
        }

        /** @var Directory $child */
        foreach ($directory->getChildren() as $child) {
            $this->hardDelete($child);
        }

        /** @var File $file */
        foreach ($directory->getFiles() as $file) {
            $this->fileService->hardDelete($file, true);
        }

        $this->entityManager->remove($directory);
        $this->entityManager->flush();
    }

    /**
     * Upload a directory
     *
     * @param Directory $directory Parent directory where the upload is done
     * @param UploadedFile[] $files Array containing all files in the specified folder (flattened strucutre)
     */
    public function upload(Directory $directory, array $files): void
    {
        // The new directory that is being uploaded
        $uploadDirectory = new Directory();
        $uploadDirectory->setParent($directory);
        // Get the uploaded folder's name from the path of one of the uploaded file
        $uploadDirectory->setName(explode('/', dirname($files[0]->getClientOriginalPath()))[0]);
        $this->entityManager->persist($uploadDirectory);

        foreach ($files as $uploadedFile) {
            $relativePath = $uploadedFile->getClientOriginalPath();
            $path = dirname($relativePath);

            // Find or create (if doesn't exist) the directory where
            // the current file should be uploaded
            $dir = $this->findOrCreateDirectory($path, $uploadDirectory);

            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $mimeType = $uploadedFile->getMimeType();

            $file = new File();
            $file->setName($originalFilename);
            $file->setDirectory($dir);
            $file->setMimeType($mimeType);
            $file->setExtension($uploadedFile->guessExtension() ?? $uploadedFile->getClientOriginalExtension());
            $file->setSize($uploadedFile->getSize());

            $this->entityManager->persist($file);
            $this->entityManager->flush();

            $uploadedFile->move(DirectoryService::getRootDirectoryPathFromDir($directory), $file->getId());

//            $files[] = FileDTO::fromEntity($file)->toArray();
        }
    }

    /**
     * Build a directory tree from the given path. Creates each individual
     * directory and creates the corresponding hierarchy
     */
    private function findOrCreateDirectory(string $path, Directory $root): Directory
    {
        // Split the path into directory names
        $directories = explode('/', $path);
        if (count($directories) == 1) {
            return $root;
        }

        $prev = $root;
        // Find the directory where the file needs to be uploaded
        // by looking in the directory tree based on the file's path
        for($i = 1; $i < count($directories); $i++) {
            // Check if the current directory exists in root
            $found = false;
            foreach ($prev->getChildren() as $child) {
                if($child->getPath() === $directories[$i]) {
                    // Go down 1 level inside that directory and continue the search
                    $prev = $child;
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                // Create a new directory and continue inside it
                $new  = new Directory();
                $new->setParent($prev);
                $new->setName($directories[$i]);
                $this->entityManager->persist($new);
                $prev = $new;
            }
        }

        return $prev;
    }

    private function rootDirectoryExists(UserInterface $user): bool
    {
        $path = static::getRootDirectoryPathFromUser($user);
        return $this->filesystem->exists($path);
    }
}
