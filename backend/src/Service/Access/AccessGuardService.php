<?php

namespace Fileknight\Service\Access;

use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Service\Access\Exception\FolderAccessDeniedException;
use Fileknight\Service\Access\Exception\FileAccessDeniedException;

class AccessGuardService
{
    public function __construct()
    {
    }

    /**
     * Check if the user has access to the directory
     * @throws FolderAccessDeniedException
     */
    public static function assertDirectoryAccess(Directory $directory, User $user): void
    {
        // Only the root directory has the owner
        $owner = $directory->getOwner() ?? $directory->getRoot()->getOwner();
        if ($owner !== $user) {
            throw new FolderAccessDeniedException($directory);
        }
    }

    /**
     * Check if the user has access to the file
     * @throws FileAccessDeniedException
     */
    public static function assertFileAccess(File $file, User $user): void
    {
        $owner = $file->getDirectory()->getRoot()->getOwner();
        if ($owner !== $user) {
            throw new FileAccessDeniedException($file);
        }
    }
}
