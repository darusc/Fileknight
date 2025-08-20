<?php

namespace Fileknight\Controller;

use Exception;
use Fileknight\ApiResponse;
use Fileknight\Controller\Traits\DirectoryResolverTrait;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\DTO\FileDTO;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Exception\FileAccessDeniedException;
use Fileknight\Exception\FileNotFoundException;
use Fileknight\Repository\FileRepository;
use Fileknight\Service\AccessGuardService;
use Fileknight\Service\File\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files')]
#[IsGranted("ROLE_USER")]
class FileController extends AbstractController
{
    use DirectoryResolverTrait;
    use UserEntityGetterTrait;

    public function __construct(
        private readonly FileService         $fileService,
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
            AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

            $content = $this->fileService->list($directory);

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
    public function create(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $directory = $this->resolveRequestDirectory($request->request->get('parentId'));
            AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

            $file = $this->fileService->upload($this->getUserEntity(), $directory, $uploadedFile);

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
     * Download file
     *
     * ```
     * GET /api/files/files/{id}/download
     * ```
     */
    #[Route(path: '/files/{id}/download', name: 'api.files.download', methods: ['GET'])]
    public function download(string $id): BinaryFileResponse|JsonResponse
    {
        try {
            $file = $this->fileRepository->find(['id' => $id]);
            FileService::assertFileExists($file);
            AccessGuardService::assertFileAccess($file, $this->getUserEntity());

            $path = $this->fileService->getFilePath($this->getUserEntity(), $file);

            $response = new BinaryFileResponse($path);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getName());

            return $response;
        } catch (FileAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch(FileNotFoundException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update file - rename / move
     *
     * ```
     * POST /api/files/files/{id}
     * {
     *     parentId: {parentId} - The file's new parent folder
     *     name: {name} - The file's new name
     * }
     * ```
     */
    #[Route('/files/{id}', name: 'api.files.update', methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $file = $this->fileRepository->find(['id' => $id]);
            FileService::assertFileExists($file);
            AccessGuardService::assertFileAccess($file, $this->getUserEntity());

            $content = json_decode($request->getContent(), true);
            $name = $content['name'] ?? null;
            $parentId = $content['parentId'] ?? null;

            if ($name === null && $parentId === null) {
                return ApiResponse::error([], 'File new name or new parent id is required.', Response::HTTP_BAD_REQUEST);
            }

            $newParent = null;
            if ($parentId != null) {
                $newParent = $this->resolveRequestDirectory($parentId);
            }

            $this->fileService->update($file, $newParent, $name);

            return ApiResponse::success([$name, ...FileDTO::fromEntity($file)->toArray()], 'File updated successfully.');
        } catch (FileAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  Delete a file
     *
     * ```
     * DELETE /api/files/files/{id}
     * ```
     */
    #[Route('/files/{id}', name: 'api.files.delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $file = $this->fileRepository->find(['id' => $id]);
            FileService::assertFileExists($file);
            AccessGuardService::assertFileAccess($file, $this->getUserEntity());

            $this->fileService->delete($this->getUserEntity(), $file);

            return ApiResponse::success(
                [],
                "File $id deleted successfully.",
            );
        } catch (FileAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
