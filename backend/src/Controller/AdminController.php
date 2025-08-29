<?php

namespace Fileknight\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Form\UserType;
use Fileknight\Repository\UserRepository;
use Fileknight\Service\Admin\DiskStatisticsService;
use Fileknight\Service\Admin\Exception\UserCreationFailedException;
use Fileknight\Service\Admin\Exception\UserResetFailedException;
use Fileknight\Service\Admin\ServerInfoService;
use Fileknight\Service\Admin\UserManagementService;
use Fileknight\Service\MailService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public const LOGIN_ROUTE = 'admin.login';
    public const DASHBOARD = 'admin.dashboard';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DiskStatisticsService  $diskUtilityService,
        private readonly ServerInfoService      $serverInfoService,
        private readonly UserManagementService  $userManagementService,
    )
    {
    }

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

    #[Route('/', name: 'admin_dashboard', methods: ['GET'])]
    #[Template('admin/dashboard.html.twig')]
    public function home(): array
    {
        $fileStats = $this->diskUtilityService->getTotalStatistics();

        /** @var UserRepository $repository */
        $repository = $this->entityManager->getRepository(User::class);

        // Fetch all users and group them by the needed attention.
        // Users that requested a password reset should be the first displayed,
        // followed by the newly created accounts that are not yet registered
        // The rest of the users are the last
        $usersPendingCreationFinish = $repository->findPendingCreationFinish();
        $usersResetRequested = $repository->findRequestedReset();
        $users = $repository->findNonSpecial();
        $userCount = $repository->getCount();

        // Get usage statistic (file count, size) for every user
        $userDiskStatistics = $this->diskUtilityService->getUserStatistics($repository->findAll());

        // Get server and database info
        $serverInfo = $this->serverInfoService->getServerInfo();
        $serverDiskUsage = $this->serverInfoService->getDiskUsage();
        $serverMemoryUsage = $this->serverInfoService->getMemoryUsage();
        $serverCpuCores = $this->serverInfoService->getCPUCores();
        $serverUptime = $this->serverInfoService->getUptime();
        $dbInfo = $this->serverInfoService->getDatabaseInfo($this->entityManager->getConnection());

        return [
            'serverTimezone' => $serverInfo['timezone'],
            'users' => [...$usersResetRequested, ...$usersPendingCreationFinish, ...$users],
            'overview' => [
                'users' => [
                    'total' => $userCount,
                    'pending_resets' => count($usersResetRequested),
                ],
                'filesystem' => [
                    'total_files' => $fileStats['count'],
                    'used_space' => $fileStats['size'],
                ],
            ],
            'usageStatistics' => $userDiskStatistics,
            'server' => [
                'info' => $serverInfo,
                'disk' => $serverDiskUsage,
                'memory' => $serverMemoryUsage,
                'cpuCores' => $serverCpuCores,
                'uptime' => $serverUptime,
                'db' => $dbInfo
            ]
        ];
    }

    #[Route('/user/create', name: 'admin_create_user')]
    public function createUser(Request $request): Response
    {
        // Create a form to handle the request that has a username and email
        $form = $this->createFormBuilder()
            ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->getForm();
        $form->handleRequest($request);

        $error = null;
        $token = null;
        $lifetime = null;
        if ($form->isSubmitted()) {
            $data = $form->getData();
            try {
                [$token, $lifetime] = $this->userManagementService->create($data['username'], $data['email']);
            } catch (UserCreationFailedException $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('admin/create_user.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'userCreated' => $form->isSubmitted() && $error === null,
            'newUser' => [
                'token' => $token,
                'expiration' => $lifetime,
            ]
        ]);
    }

    #[Route('/user/{id}/reset', name: 'admin_reset_user', methods: ['POST'])]
    public function resetUser(Request $request, string $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        try {
            [$token, $lifetime] = $this->userManagementService->reset($user);

            $this->addFlash('reset_success', true);
            $this->addFlash('reset_token', $token);
            $this->addFlash('reset_id', $user->getId());
            $this->addFlash('reset_expires', $lifetime);
        } catch (UserResetFailedException $e) {
            $this->addFlash('reset_error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/user/{id}/delete', name: 'admin_delete_user', methods: ['POST'])]
    public function deleteUser(Request $request, string $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        $this->userManagementService->delete($user);

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/logout', name: 'admin.logout')]
    public function logout(): void
    {
    }
}
