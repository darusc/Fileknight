<?php

namespace Fileknight\Controller;

use Fileknight\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/files')]
#[IsGranted("ROLE_USER")]
class FileController extends AbstractController
{
    public function __construct(
        private readonly FileService $fileService,
    )
    {
    }

    #[Route(path: '', name: 'files', methods: ['GET'])]
    public function get(): JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse([
           'path' => $this->fileService->getRootDirectoryPath($user),
        ]);
    }
}
