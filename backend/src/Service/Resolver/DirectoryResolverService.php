<?php

namespace Fileknight\Service\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Resolves the request directory based on the given id.
 * Used with requests that have a json body where the folder id is optional
 *
 * If id is null return the root directory for the current logged-in user
 */
readonly class DirectoryResolverService
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security               $security
    )
    {
    }

    /**
     * @throws NonUniqueResultException
     * @throws FolderNotFoundException
     */
    public function resolve(?string $id): Directory
    {
        /** @var DirectoryRepository $directoryRepository */
        $directoryRepository = $this->em->getRepository(Directory::class);

        if ($id === null) {
            /** @var User $user */
            $user = $this->security->getUser();
            $directory = $directoryRepository->findRootByUser($user);
        } else {
            $directory = $directoryRepository->findOneBy(['id' => $id]);
        }

        if ($directory === null) {
            throw new FolderNotFoundException($id ?? 'root');
        }

        return $directory;
    }
}
