<?php

namespace Fileknight\Service\File;

use Doctrine\Common\Collections\ArrayCollection;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use ZipArchive;

class DownloadService
{
    /**
     * Download one or more files or folders.
     * If there is only 1 file to download it will be downloaded directly,
     * otherwise a zip file will be created.
     *
     * @return array [0] -> The path to the zip archive/file to be downloaded,
     *               [1] -> name of the file for single file downloads, empty string for multiple (zip)
     */
    public function getDownloadPath(ArrayCollection $folders, ArrayCollection $files): array
    {
        if (count($files) === 1 && count($folders) === 0) {
            // If there is only 1 file id provided download that as a single file
            return [FileService::getPhysicalPath($files[0]), $files[0]->getName() . '.' . $files[0]->getExtension()];
        } else {
            // Otherwise add all the files and folders in a zip archive and download the archive

            // Create a new temporary zip archive
            $zip = $this->createZipArchive();

            // Add all files in the archive
            /** @var File $file */
            foreach ($files as $file) {
                $this->addFileToZipRoot($zip, $file);
            }

            // Add all folders in the archive
            /** @var Directory $folder */
            foreach ($folders as $folder) {
                // Parent is null. The zip archive will be the root and all folders added are its direct children
                echo 'adding folder ' . $folder->getName() . "in root\n";
                $this->addFolderToZip($zip, $folder, $folder);
            }

            return [$zip->filename, ''];
        }
    }

    /**
     * Creates a temporary zip archive
     */
    private function createZipArchive(): ZipArchive
    {
        $zip = new ZipArchive();
        $path = sys_get_temp_dir() . '/download_' . date('Ymd_His') . '.zip';
        $zip->open($path, ZipArchive::CREATE);

        return $zip;
    }

    private function addFileToZipRoot(ZipArchive $zip, File $file): void
    {
        $path = FileService::getPhysicalPath($file);
        $zip->addFile($path, $file->getName() . '.' . $file->getExtension());
    }

    private function addFileToZip(ZipArchive $zip, File $file, Directory $ascendant): void
    {
        $path = FileService::getPhysicalPath($file);
        $virtualPath = $file->getPathFromAscendant($ascendant);
        echo 'added file ' . $virtualPath . " in directory " . $ascendant->getName() . PHP_EOL;
        $zip->addFile($path, trim($virtualPath, '/') . '.' . $file->getExtension());
    }

    /**
     * Recursively add a folder to a zip archive
     */
    private function addFolderToZip(ZipArchive $zip, Directory $directory, Directory $ascendant): void
    {
        // Recurse over all the children of this directory and add them under current directory
        foreach ($directory->getChildren() as $child) {
            echo 'adding folder ' . $child->getName() . " in " . $directory->getName() . "\n";
            $this->addFolderToZip($zip, $child, $ascendant);
        }

        // Add all the files contained in the directory
        foreach ($directory->getFiles() as $file) {
            echo 'adding file ' . $file->getName() . " in " . $directory->getName() . "\n";
            $this->addFileToZip($zip, $file, $ascendant);
        }
    }
}
