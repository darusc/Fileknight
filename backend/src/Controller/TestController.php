<?php

namespace Fileknight\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TestController extends AbstractController
{
    #[Route('/test', name: 'api.test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
        ]);
    }
}
