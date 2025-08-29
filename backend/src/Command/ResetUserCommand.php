<?php

namespace Fileknight\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Service\Admin\Exception\UserResetFailedException;
use Fileknight\Service\Admin\UserManagementService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'admin:reset-user',
    description: "Resets a user's token",
)]
class ResetUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManagementService  $userManager
    )
    {
        parent::__construct('reset-user');
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'Username must be unique');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $username = $input->getArgument('username');
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($user === null) {
                throw new UserResetFailedException($username, "User not found.");
            }

            [$token, $lifetime] = $this->userManager->reset($user);

            $io->block(
                "User $username's token reset.\nNew token: $token, valid for $lifetime seconds",
                null,
                'fg=white;bg=green;',
                ' ',
                true
            );
            return self::SUCCESS;
        } catch (\Exception $e) {
            $io->block(
                "User reset failed.\nError: {$e->getMessage()}",
                null,
                'fg=white;bg=red;',
                ' ',
                true
            );
            return self::FAILURE;
        }
    }
}
