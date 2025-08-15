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

    /**
     * List the content of the directory given by the folderId query param.
     * If folderId is not given return the content of root directory.
     */
    #[Route(path: '', name: 'api.files', methods: ['GET'])]
    public function listContent(Request $request): JsonResponse
    {
        $folderId = $request->query->get('folderId');

        try {
            /** @var User $user */
            $user = $this->getUser();

            // If folderId is not given list the content of the root directory
            // otherwise list that content of the directory with that id
            if($folderId === null) {
                $directory = $this->directoryRepository->findRootByUser($user);
            } else {
                $directory = $this->directoryRepository->findOneBy(['id' => $folderId]);

                if ($directory === null) {
                    throw new DirectoryNotFoundException($folderId);
                }

                // Get the directory's root to check owner
                $root = $this->fileService->getRootFromDirectory($directory);
                if ($root->getOwner() !== $user) {
                    return new JsonResponse(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
                }
            }

            $content = $this->fileService->getDirectoryContent($directory);

            return new JsonResponse($content->toArray(), Response::HTTP_OK);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Upload file to directory given by the folderId query param.
     * If folderId is not given uploads to root directory.
     */
    #[Route(path: '', name: 'api.files.upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $folderId = $request->query->get('folderId');

        try {
            /** @var User $user */
            $user = $this->getUser();

            // If folderId is not given upload file in the root directory
            // otherwise upload in the directory with that id
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

    /**
     * Creates a new directory inside the directory given by folderId query param.
     * If folderId is not given the new directory is created inside root.
     */
    #[Route(path: '/create', name: 'api.files.create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $folderId = $request->query->get('folderId');

        $name = $request->request->get('name');
        if($name === null) {
            return new JsonResponse(['error' => 'Folder name must be specified'], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->getUser();

            // If folderId is specified, create the new folder in that folder
            // otherwise create it in the root folder
            if ($folderId === null) {
                $directory = $this->directoryRepository->findRootByUser($user);
            } else {
                $directory = $this->directoryRepository->findOneBy(['id' => $folderId]);
                if ($directory === null) {
                    throw new DirectoryNotFoundException($folderId);
                }
            }

            $this->fileService->createDirectory($directory, $name);

            return new JsonResponse(['success' => "Directory $name created successfully."], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
