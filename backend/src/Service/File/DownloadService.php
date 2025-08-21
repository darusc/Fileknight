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
     * @return string The path to the zip archive/file to be downloaded
     */
    public function getDownloadPath(ArrayCollection $folders, ArrayCollection $files): string
    {
        if (count($files) === 1 && count($folders) === 0) {
            // If there is only 1 file id provided download that as a single file
            return FileService::getPhysicalPath($files[0]);
        } else {
            // Otherwise add all the files and folders in a zip archive and download the archive

            // Create a new temporary zip archive
            $zip = $this->createZipArchive();

            // Add all files in the archive
            /** @var File $file */
            foreach ($files as $file) {
                $this->addFileToZip($zip, $file);
            }

            // Add all folders in the archive
            /** @var Directory $folder */
            foreach ($folders as $folder) {
                // Parent is null. The zip archive will be the root and all folders added are its direct children
                $this->addFolderToZip($zip, $folder);
            }

            return $zip->filename;
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

    private function addFileToZip(ZipArchive $zip, File $file, ?Directory $parent = null): void
    {
        $path = FileService::getPhysicalPath($file);
        $zip->addFile($path, ($parent !== null ? $parent->getPath() . '/' : '') . $file->getName());
    }

    /**
     * Recursively add a folder to a zip archive
     */
    private function addFolderToZip(ZipArchive $zip, Directory $directory, ?Directory $parent = null): void
    {
        // Create a new empty directory
        $zip->addEmptyDir(($parent !== null ? $parent->getPath() . '/' : '') . $directory->getName());

        // Recurse over all the children of this directory and add them under current directory
        foreach ($directory->getChildren() as $child) {
            $this->addFolderToZip($zip, $child, $directory);
        }

        // Add all the files contained in the directory
        foreach ($directory->getFiles() as $file) {
            $this->addFileToZip($zip, $file, $directory);
        }
    }
}
