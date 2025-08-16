<?php

namespace Fileknight\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Fileknight\ApiResponse;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\DTO\FileDTO;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Exception\FileAccessDeniedException;
use Fileknight\Exception\FileNotFoundException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Repository\FileRepository;
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
        private readonly FileRepository      $fileRepository,
    )
    {
    }

    /**
     * List the content of the directory given by the parentId query param.
     * If parentId is not given return the content of root directory.
     *
     *  ```
     *  GET /api/files?parentId={id}
     *  ```
     */
    #[Route(path: '', name: 'api.files', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $directory = $this->resolveRequestDirectory($request->query->get('parentId'));
            $content = $this->fileService->getDirectoryContent($directory);

            return ApiResponse::success($content->toArray());
        } catch (DirectoryAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload file
     *
     * ```
     * POST /api/files
     * {
     *     file: <file>
     *     parentId: {parentId} - The folder in which to upload it. If not specified upload in root
     *     name: {name} - The name to upload the file with. If not specified use the original file name
     * }
     * ```
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
            $directory = $this->resolveRequestDirectory($request->request->get('parentId'));
            $file = $this->fileService->uploadFile($user, $directory, $uploadedFile);

            return ApiResponse::success(
                FileDTO::fromEntity($file)->toArray(),
                'File uploaded successfully.',
            );
        } catch (DirectoryAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ```
     * DELETE /api/files/files/{id}
     * ```
     *
     * Delete a file
     */
    #[Route('/files/{id}', name: 'api.files.delete', methods: ['DELETE'])]
    public function deleteFile(Request $request, string $id): JsonResponse
    {
        try {
            $file = $this->fileRepository->find(['id' => $id]);
            $this->assertFileExistenceOwnership($file, $id);

            $this->fileService->deleteFile($file);

            return ApiResponse::success(
                [],
                'File deleted successfully.',
            );
        } catch (FileAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ```
     * POST /api/files/folders
     * {
     *      name: <name>,
     *      parentId: {parentId}
     * }
     * ```
     *
     * Creates a new folder.
     */
    #[Route(path: '/folders', name: 'api.files.folders', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $name = $request->request->get('name');
        if ($name === null) {
            return ApiResponse::error([], 'Folder name is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $directory = $this->resolveRequestDirectory($request->request->get('parentId'));
            $created = $this->fileService->createDirectory($directory, $name);

            return ApiResponse::success(
                DirectoryDTO::fromEntity($created)->toArray(),
                'Directory created successfully.',
            );
        } catch (DirectoryAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ```
     * DELETE /api/files/folders/{id}
     * ```
     *
     * Recursively delete a folder
     */
    #[Route('/folders/{id}', name: 'api.files.folders.delete', methods: ['DELETE'])]
    public function deleteFolder(Request $request, string $id): JsonResponse
    {
        try {
            $directory = $this->resolveRequestDirectory($id);
            $this->fileService->deleteDirectory($directory);

            return ApiResponse::success(
                [],
                'Folder deleted successfully.',
            );
        } catch (DirectoryAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resolves the request directory based on the given id.
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
