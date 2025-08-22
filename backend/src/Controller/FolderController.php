<?php

namespace Fileknight\Controller;

use Exception;
use Fileknight\ApiResponse;
use Fileknight\Controller\Traits\DirectoryResolverTrait;
use Fileknight\Controller\Traits\RequestJsonGetterTrait;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\Exception\DirectoryAccessDeniedException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\AccessGuardService;
use Fileknight\Service\File\DirectoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files/folders')]
#[IsGranted("ROLE_USER")]
class FolderController extends AbstractController
{
    use DirectoryResolverTrait;
    use UserEntityGetterTrait;
    use RequestJsonGetterTrait;

    public function __construct(
        private readonly DirectoryService    $folderService,
        private readonly DirectoryRepository $directoryRepository,
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
        $parentId = $this->getJsonField($request, 'parentId');
        $name = $this->getJsonField($request, 'name');

        if ($name === null) {
            return ApiResponse::error([], 'Folder name is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $directory = $this->resolveRequestDirectory($parentId);
            AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

            $created = $this->folderService->create($directory, $name);

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
     * Update folder - rename / move
     *
     * ```
     * PATCH /api/files/folders/{id}
     * {
     *     parentId: {parentId} - The folder's new parent folder
     *     name: {name} - The folder's new name
     * }
     * ```
     */
    #[Route('/{id}', name: 'api.files.folders.update', methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $directory = $this->directoryRepository->find(['id' => $id]);
            DirectoryService::assertDirectoryExists($directory);
            AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

            $parentId = $this->getJsonField($request, 'parentId');
            $name = $this->getJsonField($request, 'name');

            if ($name === null && $parentId === null) {
                return ApiResponse::error([], 'Folder new name or new parent id is required.', Response::HTTP_BAD_REQUEST);
            }

            $newParent = null;
            if ($parentId != null) {
                $newParent = $this->resolveRequestDirectory($parentId);
            }

            $this->folderService->update($directory, $newParent, $name);

            return ApiResponse::success(DirectoryDTO::fromEntity($directory)->toArray(), 'Folder updated successfully.');
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
    public function delete(string $id): JsonResponse
    {
        try {
            $directory = $this->directoryRepository->find(['id' => $id]);
            DirectoryService::assertDirectoryExists($directory);
            AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

            $this->folderService->delete($directory);

            return ApiResponse::success(
                [],
                "Folder $id deleted successfully.",
            );
        } catch (DirectoryAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
