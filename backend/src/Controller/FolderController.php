<?php

namespace Fileknight\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\Exception\ApiException;
use Fileknight\Response\ApiResponse;
use Fileknight\Service\Access\AccessGuardService;
use Fileknight\Service\Access\Exception\FolderAccessDeniedException;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Fileknight\Service\Resolver\DirectoryResolverService;
use Fileknight\Service\Resolver\Request\RequestResolverService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files/folders')]
#[IsGranted("ROLE_USER")]
class FolderController extends AbstractController
{
    use UserEntityGetterTrait;

    public function __construct(
        private readonly RequestResolverService   $requestResolverService,
        private readonly DirectoryResolverService $directoryResolverService,
        private readonly EntityManagerInterface   $em,
        private readonly DirectoryService         $folderService,
    )
    {
    }

    /**
     *  Creates a new folder.
     *
     * ```
     * POST /api/files/folders
     * {
     *      name:     (required) Folder's name
     *      parentId: (required) Folder's parent. If null create in root
     * }
     * ```
     * @throws ApiException
     * @throws NonUniqueResultException
     */
    #[Route(path: '', name: 'api.files.folders', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, ['name', 'parentId']);

        $directory = $this->directoryResolverService->resolve($data->get('parentId'));
        AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

        $created = $this->folderService->create($directory, $data->get('name'));

        return ApiResponse::success(
            DirectoryDTO::fromEntity($created)->toArray(),
            'Directory created successfully.',
        );
    }

    /**
     * Update folder - rename / move
     *
     * ```
     * PATCH /api/files/folders/{id}
     * {
     *     parentId: (optional) Folder's new parent folder. If null the new parent will be root
     *     name:     (optional) Folder's new name
     * }
     * ```
     * @throws ApiException
     * @throws NonUniqueResultException
     */
    #[Route('/{id}', name: 'api.files.folders.update', methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, [], ['name', 'parentId']);

        $directory = $this->folderService->get($id);
        AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

        // If the parentId field is specified use it to get the new parent, otherwise make it null so the file is not moved
        // Not using directly $data->get('parentId') !== null because get() returns null for nonexisting fields
        $newParent = $data->exists('parentId') ?
            $directory = $this->directoryResolverService->resolve($data->get('parentId')) :
            null;

        $this->folderService->update($directory, $newParent, $data->get('name'));

        return ApiResponse::success(DirectoryDTO::fromEntity($directory)->toArray(), 'Folder updated successfully.');
    }

    /**
     * Recursively delete a folder
     *
     * ```
     * DELETE /api/files/folders/{id}
     * ```
     * @throws FolderAccessDeniedException
     * @throws FolderNotFoundException
     */
    #[Route('/{id}', name: 'api.files.folders.delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $directory = $this->folderService->get($id);
        AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

        $this->folderService->delete($directory);

        return ApiResponse::success(
            [],
            "Folder $id deleted successfully.",
        );
    }
}
