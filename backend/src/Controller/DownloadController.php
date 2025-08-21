<?php

namespace Fileknight\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Fileknight\ApiResponse;
use Fileknight\Controller\Traits\RequestJsonGetterTrait;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\Exception\FileAccessDeniedException;
use Fileknight\Exception\FileNotFoundException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Repository\FileRepository;
use Fileknight\Service\AccessGuardService;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\File\DownloadService;
use Fileknight\Service\File\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/files/download')]
class DownloadController extends AbstractController
{
    use UserEntityGetterTrait;
    use RequestJsonGetterTrait;

    public function __construct(
        private readonly DownloadService     $downloadService,
        private readonly FileRepository      $fileRepository,
        private readonly DirectoryRepository $directoryRepository,
    )
    {
    }

    /**
     * Download 1 or more files specified by the ids field in the request body
     *
     * ```
     * POST /api/files/download
     * {
     *      "folderIds": [],
     *      "fileIds": []
     * }
     * ```
     */
    #[Route('', name: 'api.files.download', methods: ['POST'])]
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        try {
            $fileIds = $this->getJsonField($request, 'fileIds');
            $folderIds = $this->getJsonField($request, 'folderIds');
            if (empty($fileIds) && empty($folderIds)) {
                return ApiResponse::error([], 'No file or folder id provided', Response::HTTP_BAD_REQUEST);
            }

            $files = new ArrayCollection();
            if(!empty($fileIds)) {
                foreach ($fileIds as $fileId) {
                    $file = $this->fileRepository->find($fileId);
                    FileService::assertFileExists($file);
                    AccessGuardService::assertFileAccess($file, $this->getUserEntity());
                    $files->add($file);
                }
            }

            $directories = new ArrayCollection();
            if(!empty($folderIds)) {
                foreach ($folderIds as $folderId) {
                    $directory = $this->directoryRepository->find($folderId);
                    DirectoryService::assertDirectoryExists($directory);
                    AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());
                    $directories->add($directory);
                }
            }

            // Get the path to the file to send as a binary stream
            // That path is either a zip archive or a single file
            $dlpath = $this->downloadService->getDownloadPath($directories, $files);

            $response = new BinaryFileResponse($dlpath);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

            // Delete the temporary zip archive after it was sent only in production mode
            // Keep it in debug mode for debugging (will require manual delete)
            if($this->getParameter('kernel.environment') === 'prod') {
                $response->deleteFileAfterSend(true);
            }

            return $response;
        } catch (FileAccessDeniedException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_FORBIDDEN);
        } catch (FileNotFoundException $exception) {
            return ApiResponse::error([], $exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return ApiResponse::error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
