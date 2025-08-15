<?php

namespace Fileknight\Controller;

use Exception;
use Fileknight\Entity\Directory;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files')]
#[IsGranted("ROLE_USER")]
class FileController extends AbstractController
{
    public function __construct(
        private readonly FileService         $fileService,
        private readonly DirectoryRepository $directoryRepository,
    )
    {
    }

    /**
     * List the content of the directory given by the folderId query param.
     * If folderId is not given return the content of root directory.
     */
    #[Route(path: '', name: 'api.files', methods: ['GET'])]
    public function listContent(Request $request): JsonResponse
    {
        try {
            $directory = $this->resolveRequestDirectory($request);
            $content = $this->fileService->getDirectoryContent($directory);

            return new JsonResponse($content->toArray(), Response::HTTP_OK);
        } catch (DirectoryAccessDeniedException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload file to directory given by the folderId query param.
     * If folderId is not given uploads to root directory.
     */
    #[Route(path: '', name: 'api.files.upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $directory = $this->resolveRequestDirectory($request);
            $this->fileService->uploadFile($user, $directory, $uploadedFile);

            return new JsonResponse(['success' => 'File uploaded successfully.'], Response::HTTP_OK);
        } catch (DirectoryAccessDeniedException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Creates a new directory inside the directory given by folderId query param.
     * If folderId is not given the new directory is created inside root.
     */
    #[Route(path: '/create', name: 'api.files.create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        if ($name === null) {
            return new JsonResponse(['error' => 'Folder name must be specified'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $directory = $this->resolveRequestDirectory($request);
            $this->fileService->createDirectory($directory, $name);

            return new JsonResponse(['success' => "Directory $name created successfully."], Response::HTTP_OK);
        } catch (DirectoryAccessDeniedException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resolves the request directory based on the folder id.
     * If folder id is null => root directory
     * @throws DirectoryNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws DirectoryAccessDeniedException
     */
    private function resolveRequestDirectory(Request $request): Directory
    {
        $folderId = $request->query->get('folderId');
        if ($folderId === null) {
            /** @var User $user */
            $user = $this->getUser();
            $directory = $this->directoryRepository->findRootByUser($user);
        } else {
            $directory = $this->directoryRepository->findOneBy(['id' => $folderId]);
            $this->assertFolderExistenceOwnership($directory, $folderId);
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
}
