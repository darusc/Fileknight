<?php

namespace Fileknight\Controller;

use Exception;
use Fileknight\Entity\User;
use Fileknight\Exception\DirectoryNotFoundException;
use Fileknight\Repository\DirectoryRepository;
use Fileknight\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route(path: '', name: 'api.files', methods: ['GET'])]
    public function getRootContent(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $root = $this->directoryRepository->findRootByUser($user);
            $content = $this->fileService->getDirectoryContent($root);

            return new JsonResponse($content->toArray(), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/folders/{folderId}', name: 'api.files.folder', methods: ['GET'])]
    public function getDirectoryContent(string $folderId): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $directory = $this->directoryRepository->findOneBy(['id' => $folderId]);
            $root = $this->fileService->getRootFromDirectory($directory);

            if ($root->getOwner() !== $user) {
                return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
            }

            $content = $this->fileService->getDirectoryContent($directory);

            return new JsonResponse($content->toArray(), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '', name: 'api.files.upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $folderId = $request->request->get('folderId');

        try {
            /** @var User $user */
            $user = $this->getUser();

            // If folderId is specified in the request body upload the file in that folder
            // otherwise upload it in the root folder
            if ($folderId === null) {
                $directory = $this->directoryRepository->findRootByUser($user);
            } else {
                $directory = $this->directoryRepository->findOneBy(['id' => $folderId]);
                if ($directory === null) {
                    throw new DirectoryNotFoundException($folderId);
                }
            }

            $this->fileService->uploadFile($user, $directory, $uploadedFile);

            return new JsonResponse(['success' => 'File uploaded successfully.'], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
