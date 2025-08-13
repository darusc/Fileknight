<?php

namespace Fileknight\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Fileknight\Entity\User;
use Fileknight\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService $userService,
    )
    {
    }

    #[Route(path: '/register', name: 'auth.register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if ($email === null || $password === null) {
            return new JsonResponse(['error' => 'Email and password required.'], 400);
        }

        try {
            $this->userService->register($email, $password);

            return new JsonResponse(['success' => 'User successfully registered.'], 201);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
        }
    }

    #[Route(path: '/delete', name: 'auth.delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        $email = json_decode($request->getContent(), true)['email'] ?? null;
        if ($email === null) {
            return new JsonResponse(['error' => 'Email is required.'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->userService->delete($user);

        return new JsonResponse(['success' => 'User successfully deleted.'], 201);
    }

    #[Route(path: '/list', name: 'auth.list', methods: ['GET'], condition: "env('APP_ENV') == 'dev'")]
    public function list(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $res = array_map(fn($user) => (string)$user, $users);

        return new JsonResponse(['users' => $res]);
    }
}
