<?php

namespace Fileknight\Service\Admin;

use Fileknight\Entity\User;
use FilesystemIterator;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class DiskStatisticsService
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = $_ENV['USER_STORAGE_PATH'];
    }

    /**
     * Returns the total number of files in server's storage and size occupied [$count, $size]
     */
    public function getTotalStatistics(): array
    {
        return $this->getStatistics($this->storagePath);
    }

    /**
     * Get disk statistics for each user. (number of files stored, size occupied and percentage)
     * @param User[] $users
     * @return array
     */
    public function getUserStatistics(array $users): array
    {
        $total = $this->getTotalStatistics();
        $usageStatistics = [];
        foreach ($users as $user) {
            $userStats = $this->getStatistics($this->storagePath . '/' . $user->getUsername());

            $percent = $total['size'] === 0 ? 0 : $total['size'] / $userStats['size'];
            $usageStatistics[] = [
                'username' => $user->getUsername(),
                'files' => $userStats['count'],
                'size' => $userStats['size'],
                'percent' => $percent * 100
            ];
        }

        return $usageStatistics;
    }

    /**
     * Returns the number of files in the given directory path and the total size [$count, $size]
     */
    private function getStatistics(string $path): array
    {
        $count = 0;
        $size = 0;

        $rii = new \RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));

        /* @var SplFileInfo $file */
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $count++;
                $size += $file->getSize();
            }
        }

        return ['count' => $count, 'size' => $size];
    }
}
