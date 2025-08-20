<?php

namespace Fileknight\Service\File;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseFileService
{
    protected string $basepath;
    protected FileSystem $filesystem;

    public function __construct()
    {
        $this->basepath = $_ENV['USER_STORAGE_PATH'];
        $this->filesystem = new Filesystem();
    }

    protected function getRootDirectoryPath(UserInterface $user): string
    {
        return $this->basepath . '/' . $user->getUserIdentifier();
    }
}
