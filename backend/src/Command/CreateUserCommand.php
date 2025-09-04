<?php

namespace Fileknight\Command;

use Fileknight\Service\Admin\UserManagementService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'admin:create-user',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserManagementService $userManager
    )
    {
        parent::__construct('create-user');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username must be unique')
            ->addArgument('email', InputArgument::REQUIRED, 'User email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $username = $input->getArgument('username');
            $email = $input->getArgument('email');
            [$token, $lifetime] = $this->userManager->create($username, $email);

            $io->block(
                "User $username created.\nToken: $token, valid for $lifetime seconds",
                null,
                'fg=white;bg=green;',
                ' ',
                true
            );
            return self::SUCCESS;
        } catch (\Exception $e) {
            $io->block(
                "User creation failed.\nError: {$e->getMessage()}",
                null,
                'fg=white;bg=red;',
                ' ',
                true
            );
            return self::FAILURE;
        }
    }
}
