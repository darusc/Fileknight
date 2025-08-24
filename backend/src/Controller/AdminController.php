<?php

namespace Fileknight\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public const LOGIN_ROUTE = 'admin.login';
    public const DASHBOARD = 'admin.dashboard';

    #[Route('/login', name: self::LOGIN_ROUTE)]
    #[Template('admin/login.html.twig')]
    public function login(AuthenticationUtils $authenticationUtils): array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'controller_name' => 'AdminController',
            'last_username' => $lastUsername,
            'error' => $error
        ];
    }

    #[Route('/', methods: ['GET'])]
    #[Template('base.html.twig')]
    public function home(): array
    {
        return [];
    }

    #[Route('/logout', name: 'admin.logout')]
    public function logout(): void
    {
    }
}
