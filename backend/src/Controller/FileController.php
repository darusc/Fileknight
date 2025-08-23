<?php

namespace Fileknight\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\DTO\FileDTO;
use Fileknight\Exception\ApiException;
use Fileknight\Response\ApiResponse;
use Fileknight\Service\Access\AccessGuardService;
use Fileknight\Service\Access\Exception\FileAccessDeniedException;
use Fileknight\Service\Access\Exception\FolderAccessDeniedException;
use Fileknight\Service\File\Exception\FileNotFoundException;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Fileknight\Service\File\FileService;
use Fileknight\Service\Resolver\DirectoryResolverService;
use Fileknight\Service\Resolver\Request\RequestResolverService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files')]
#[IsGranted("ROLE_USER")]
class FileController extends AbstractController
{
    use UserEntityGetterTrait;

    public function __construct(
        private readonly RequestResolverService   $requestResolverService,
        private readonly DirectoryResolverService $directoryResolverService,
        private readonly EntityManagerInterface   $em,
        private readonly FileService              $fileService,
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
     * @throws FolderAccessDeniedException
     * @throws FolderNotFoundException
     * @throws NonUniqueResultException
     */
    #[Route(path: '', name: 'api.files', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $directory = $this->directoryResolverService->resolve($request->query->get('parentId'));
        AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

        $content = $this->fileService->list($directory);

        return ApiResponse::success($content->toArray());
    }

    /**
     * Upload file
     *
     * ```
     * POST /api/files
     * {
     *     file:     <file>
     *     parentId: (required) The folder in which to upload it. If null upload in root
     *     name:     (optional) The name to upload the file with. If not specified or null use the original file name
     * }
     * ```
     * @throws ApiException
     * @throws NonUniqueResultException
     */
    #[Route(path: '', name: 'api.files.upload', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, ['parentId'], ['name'], ['file']);

        $directory = $this->directoryResolverService->resolve($data->get('parentId'));
        AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());

        $file = $this->fileService->upload($directory, $data->get('file'));

        return ApiResponse::success(FileDTO::fromEntity($file)->toArray(), 'File uploaded successfully.');
    }

    /**
     * Update file - rename / move
     *
     * ```
     * PATCH /api/files/{id}
     * {
     *     parentId: (optional) The file's new parent folder. If null new parent is root
     *     name:     (optional) The file's new name
     * }
     * ```
     * @throws ApiException
     * @throws NonUniqueResultException
     */
    #[Route('/{id}', name: 'api.files.update', methods: ['PATCH'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, [], ['parentId', 'name']);

        $file = $this->fileService->get($id);
        AccessGuardService::assertFileAccess($file, $this->getUserEntity());

        // If the parentId field is specified use it to get the new parent, otherwise make it null so the file is not moved
        // Not using directly $data->get('parentId') !== null because get() returns null for nonexisting fields
        $newParent = $data->exists('parentId') ?
            $this->directoryResolverService->resolve($data->get('parentId')) :
            null;

        $this->fileService->update($file, $newParent, $data->get('name'));

        return ApiResponse::success(FileDTO::fromEntity($file)->toArray(), 'File updated successfully.');
    }

    /**
     *  Delete a file
     *
     * ```
     * DELETE /api/files/{id}
     * ```
     * @throws FileAccessDeniedException
     * @throws FileNotFoundException
     */
    #[Route('/{id}', name: 'api.files.delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $file = $this->fileService->get($id);
        AccessGuardService::assertFileAccess($file, $this->getUserEntity());

        $this->fileService->delete($file);

        return ApiResponse::success([], "File $id deleted successfully.");
    }
}
