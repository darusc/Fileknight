<?php

namespace Fileknight\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Fileknight\Controller\Traits\UserEntityGetterTrait;
use Fileknight\Exception\ApiException;
use Fileknight\Response\ApiResponse;
use Fileknight\Service\Access\AccessGuardService;
use Fileknight\Service\File\DirectoryService;
use Fileknight\Service\File\DownloadService;
use Fileknight\Service\File\FileService;
use Fileknight\Service\Resolver\Request\RequestResolverService;
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

    public function __construct(
        private readonly RequestResolverService $requestResolverService,
        private readonly DownloadService        $downloadService,
        private readonly FileService            $fileService,
        private readonly DirectoryService       $directoryService,
    )
    {
    }

    /**
     * Download 1 or more files specified by the ids field in the request body
     *
     * ```
     * POST /api/files/download
     * {
     *      folderIds: (optional) Array containing the ids of the folders to download
     *      fileIds:   (optional) Array containing the ids of the files to download
     * }
     * ```
     * @throws ApiException
     */
    #[Route('', name: 'api.files.download', methods: ['POST'])]
    public function download(Request $request): BinaryFileResponse|JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, [], ['folderIds', 'fileIds']);

        if (empty($data->get('fileIds')) && empty($data->get('folderIds'))) {
            return ApiResponse::error(
                'NO_DOWNLOAD_TARGETS',
                'At least one target (folder or file) must be specified',
                Response::HTTP_BAD_REQUEST
            );
        }

        $files = new ArrayCollection();
        if (!empty($data->get('fileIds'))) {
            foreach ($data->get('fileIds') as $fileId) {
                $file = $this->fileService->get($fileId);
                AccessGuardService::assertFileAccess($file, $this->getUserEntity());
                $files->add($file);
            }
        }

        $directories = new ArrayCollection();
        if (!empty($data->get('folderIds'))) {
            foreach ($data->get('folderIds') as $folderId) {
                $directory = $this->directoryService->get($folderId);
                AccessGuardService::assertDirectoryAccess($directory, $this->getUserEntity());
                $directories->add($directory);
            }
        }

        // Get the path to the file to send as a binary stream
        // That path is either a zip archive or a single file
        [$dlpath, $dlname] = $this->downloadService->getDownloadPath($directories, $files);

        $response = new BinaryFileResponse($dlpath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $dlname);

        // Delete the temporary zip archive after it was sent only in production mode
        // Keep it in debug mode for debugging (will require manual delete)
        if ($this->getParameter('kernel.environment') === 'prod') {
            $response->deleteFileAfterSend(true);
        }

        return $response;
    }
}
