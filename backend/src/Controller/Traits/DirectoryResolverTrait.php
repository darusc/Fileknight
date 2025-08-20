<?php

namespace Fileknight\Controller\Traits;

use Doctrine\ORM\NonUniqueResultException;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Exception\FileAccessDeniedException;
use Fileknight\Exception\FileNotFoundException;

trait DirectoryResolverTrait
{
    /**
     * Resolves the request directory based on the given id and asserts existence and ownership
     * @param string|null $id If it is null return the root directory
     *
     * @throws DirectoryAccessDeniedException
     * @throws DirectoryNotFoundException
     * @throws NonUniqueResultException
     */
    private function resolveRequestDirectory(?string $id): Directory
    {
        if ($id === null) {
            /** @var User $user */
            $user = $this->getUser();
            $directory = $this->directoryRepository->findRootByUser($user);
        } else {
            $directory = $this->directoryRepository->findOneBy(['id' => $id]);
            $this->assertFolderExistenceOwnership($directory, $id);
        }

        return $directory;
    }

    /**
     * Asserts that given directory exists (is not null) and is
     * in the ownership of the currently logged in user
     * @throws DirectoryAccessDeniedException
     * @throws DirectoryNotFoundException
     */
    private function assertFolderExistenceOwnership(?Directory $directory, string $folderId): void
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($directory === null) {
            throw new DirectoryNotFoundException($folderId);
        }

        // Get the directory's root to check owner
        $root = $this->fileService->getRootFromDirectory($directory);
        if ($root->getOwner() !== $user) {
            throw new DirectoryAccessDeniedException($folderId);
        }
    }

    /**
     *  Asserts that given file exists (is not null) and is
     *  in the ownership of the currently logged in user
     * @throws FileNotFoundException
     * @throws FileAccessDeniedException
     */
    private function assertFileExistenceOwnership(?File $file, string $fileId): void
    {
        /** @var User $user */
        $user = $this->getUser();

        if($file === null) {
            throw new FileNotFoundException($fileId);
        }

        $root = $this->fileService->getRootFromDirectory($file->getDirectory());
        if ($root->getOwner() !== $user) {
            throw new FileAccessDeniedException($file);
        }
    }
}
