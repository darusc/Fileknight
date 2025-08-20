<?php

namespace Fileknight\Controller;

use Exception;
use Fileknight\ApiResponse;
use Fileknight\Controller\Traits\DirectoryResolverTrait;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Repository\FileRepository;
use Fileknight\Service\FileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files/folders')]
#[IsGranted("ROLE_USER")]
class FolderController
{
    use DirectoryResolverTrait;

    public function __construct(
        private readonly FileService         $fileService,
        private readonly DirectoryRepository $directoryRepository,
        private readonly FileRepository      $fileRepository,
    )
    {
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
    #[Route(path: '', name: 'api.files.folders', methods: ['POST'])]
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
    #[Route('/{id}', name: 'api.files.folders.delete', methods: ['DELETE'])]
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
}
