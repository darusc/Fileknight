<?php

namespace Fileknight\Controller\Traits;

use Doctrine\ORM\NonUniqueResultException;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Repository\DirectoryRepository;

trait DirectoryResolverTrait
{
    /**
     * Resolves the request directory based on the given id.
     * Used with requests that have a json body where the folder id is optional
     * @param string|null $id If it is null return the root directory
     *
     * @throws NonUniqueResultException
     * @throws DirectoryNotFoundException
     */
    private function resolveRequestDirectory(?string $id): Directory
    {
        /** @var DirectoryRepository $directoryRepository */
        $directoryRepository = $this->em->getRepository(Directory::class);

        if ($id === null) {
            /** @var User $user */
            $user = $this->getUser();
            $directory = $directoryRepository->findRootByUser($user);
        } else {
            $directory = $directoryRepository->findOneBy(['id' => $id]);
        }

        if ($directory === null) {
            throw new DirectoryNotFoundException($id ?? 'root');
        }

        return $directory;
    }
}
