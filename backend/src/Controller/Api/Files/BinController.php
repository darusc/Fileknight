<?php

namespace Fileknight\Controller\Api\Files;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\DTO\DirectoryContentDTO;
use Fileknight\DTO\DirectoryDTO;
use Fileknight\DTO\FileDTO;
use Fileknight\Entity\Directory;
use Fileknight\Entity\File;
use Fileknight\Exception\ApiException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Repository\FileRepository;
use Fileknight\Response\ApiResponse;
use Fileknight\Service\Access\AccessGuardService;
use Fileknight\Service\Access\Exception\FolderAccessDeniedException;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\File\Exception\FolderNotFoundException;
use Fileknight\Service\File\FileService;
use Fileknight\Service\Resolver\Request\RequestResolverService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/bin')]
#[IsGranted("ROLE_USER")]
class BinController extends AbstractController
{
    use UserEntityGetterTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileService            $fileService,
        private readonly DirectoryService       $directoryService,
        private readonly RequestResolverService $requestResolverService,
    )
    {
    }

    /**
     * List all binned items
     *
     * ```
     * GET /api/bin
     * ```
     */
    #[Route(path: '/', name: 'api.bin', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = $this->entityManager->getRepository(File::class);
        /** @var DirectoryRepository $directoryRepository */
        $directoryRepository = $this->entityManager->getRepository(Directory::class);

        /** @var FileDTO[] $files */
        $files = [];
        foreach ($fileRepository->findAll() as $file) {
            $files[] = FileDTO::fromEntity($file);
        }

        /** @var DirectoryDTO[] $directories */
        $directories = [];
        foreach ($directoryRepository->findAll() as $directory) {
            $directories[] = DirectoryDTO::fromEntity($directory);
        }

        $content = new DirectoryContentDTO('bin', 'bin', $files, $directories);

        return ApiResponse::success($content);
    }

    /**
     * Restore given items
     *
     * ```
     * POST /api/bin/restore
     * {
     *     fileIds:   (optional)
     *     folderIds: (optional)
     * }
     * ```
     * @throws ApiException
     */
    #[Route(path: '/restore', name: 'api.bin.restore', methods: ['POST'])]
    public function restore(Request $request): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, [], ['folderIds', 'fileIds']);

        if (empty($data->get('fileIds')) && empty($data->get('folderIds'))) {
            return ApiResponse::error(
                'NO_RESTORE_TARGETS',
                'At least one target (folder or file) must be specified',
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!empty($data->get('fileIds'))) {
            foreach ($data->get('fileIds') as $fileId) {
                $file = $this->fileService->get($fileId);
                AccessGuardService::assertFileAccess($file, $this->getUserEntity());
                $this->fileService->restore($file);
            }
        }

        if (!empty($data->get('folderIds'))) {
            foreach ($data->get('folderIds') as $folderId) {
                $directory = $this->directoryService->get($folderId);
                AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());
                $this->directoryService->restore($directory);
            }
        }

        return ApiResponse::success([], 'Files restored successfully');
    }

    /**
     * Hard deletes the given items. Permanently removes the files from the system
     * and deletes all database entries
     *
     * ```
     * POST /api/bin/delete
     * {
     *     fileIds:   (optional)
     *     folderIds: (optional)
     * }
     * ```
     * @throws ApiException
     */
    #[Route(path: '/delete', name: 'api.bin.delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, [], ['folderIds', 'fileIds']);

        if (empty($data->get('fileIds')) && empty($data->get('folderIds'))) {
            return ApiResponse::error(
                'NO_DELETE_TARGETS',
                'At least one target (folder or file) must be specified',
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!empty($data->get('fileIds'))) {
            foreach ($data->get('fileIds') as $fileId) {
                $file = $this->fileService->get($fileId);
                AccessGuardService::assertFileAccess($file, $this->getUserEntity());
                $this->fileService->hardDelete($file);
            }
        }

        if (!empty($data->get('folderIds'))) {
            foreach ($data->get('folderIds') as $folderId) {
                $directory = $this->directoryService->get($folderId);
                AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());
                $this->directoryService->hardDelete($directory);
            }
        }

        return ApiResponse::success([], 'Files restored successfully');
    }

    /**
     * Empty the bin. Hard deletes everything
     *
     * ```
     * DELETE /api/bin/empty
     * ```
     */
    #[Route(path: '/empty', name: 'api.bin.empty', methods: ['DELETE'])]
    public function empty(): JsonResponse
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = $this->entityManager->getRepository(File::class);
        /** @var DirectoryRepository $directoryRepository */
        $directoryRepository = $this->entityManager->getRepository(Directory::class);

        foreach ($fileRepository->findAll() as $file) {
            $this->fileService->hardDelete($file);
        }

        foreach ($directoryRepository->findAll() as $directory) {
            $this->directoryService->hardDelete($directory);
        }

        return ApiResponse::success([], "Bin emptied successfully.");
    }
}
