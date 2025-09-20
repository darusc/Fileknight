<?php

namespace Fileknight\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, File::class);
    }

    /**
     * Get the next index for duplicate filename.
     * If "file.txt" already exists and it is uploaded
     * again the function will return 1 "file (1).txt"
     * It returns the highest index found.
     *
     * @param Directory $directory Directory in which the file is searched
     * @param string $filename The file's name to search for
     */
    public function findNextFilenameIndex(Directory $directory, string $filename): int
    {
        $pattern = $filename . '%';

        $query = $this->createQueryBuilder('f')
            ->select('f.name')
            ->where('f.directory = :directory')
            ->andWhere('f.name LIKE :pattern')
            ->setParameter('directory', $directory)
            ->setParameter('pattern', $pattern)
            ->getQuery();

        $result = $query->getScalarResult();
        $names = array_column($result, 'name');

        // Find the maximum index in the found names
        $index = 0;
        foreach ($names as $name) {
            if (preg_match('/\((\d+)\)$/', $name, $matches)) {
                $index = max($index, (int)$matches[1]);
            }
        }

        return $index;
    }

    /**
     * @return File[]
     */
    public function findAllBinned(): array {
        $query = $this->createQueryBuilder('f')
            ->select('*')
            ->where('f.deletedAt IS NOT NULL')
            ->addOrderBy('f.deletedAt', 'DESC')
            ->addOrderBy('f.name', 'ASC')
            ->getQuery();

        return $query->getResult();
    }
}
