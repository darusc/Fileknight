<?php

namespace Fileknight\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\DTO\UserDTO;
use Fileknight\Entity\User;
use Fileknight\Exception\ApiException;
use Fileknight\Response\ApiResponse;
use Fileknight\Service\Resolver\Request\RequestResolverService;
use Fileknight\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserService            $userService,
        private readonly RequestResolverService $requestResolverService
    )
    {
    }

    /**
     * Register new user. This is used for finishing the registration process.
     * The user entity is created by the server's admin, this api just sets
     * finished registration (sets the password) using the received token
     *
     * ```
     * POST /api/auth/register
     * {
     *      username: (required) User's unique username
     *      password: (required) User's password
     *      registrationToken: (required) Token used for registration. Received from server admin
     * }
     * ```
     * @throws ApiException
     */
    #[Route(path: '/register', name: 'auth.register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = $this->requestResolverService->resolve($request, ['username', 'registrationToken', 'password']);

        $user = $this->userService->getUser($data->get('username'));
        $this->userService->register($user, $data->get('password'), $data->get('registrationToken'));

        return ApiResponse::success(
            UserDTO::fromEntity($user)->toArray(),
            'User successfully registered.',
            Response::HTTP_CREATED
        );
    }
}
