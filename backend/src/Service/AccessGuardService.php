<?php

namespace Fileknight\Service;

use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Exception\FileAccessDeniedException;
use Fileknight\Service\File\DirectoryService;

class AccessGuardService
{
    public function __construct()
    {
    }

    /**
     * Check if the user has access to the directory
     * @throws DirectoryAccessDeniedException
     */
    public static function assertDirectoryAccess(Directory $directory, User $user): void
    {
        // Only the root directory has the owner
        $owner = $directory->getOwner() ?? DirectoryService::getRootFromDirectory($directory)->getOwner();

        if ($owner !== $user) {
            throw new DirectoryAccessDeniedException($directory->getId());
        }
    }

    /**
     * Check if the user has access to the file
     * @throws FileAccessDeniedException
     */
    public static function assertFileAccess(File $file, User $user): void
    {
        $owner = DirectoryService::getRootFromDirectory($file->getDirectory())->getOwner();
        if ($owner !== $user) {
            throw new FileAccessDeniedException($file);
        }
    }
}
