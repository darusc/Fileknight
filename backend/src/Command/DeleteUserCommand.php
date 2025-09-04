<?php

namespace Fileknight\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\User;
use Fileknight\Service\Admin\UserManagementService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'admin:delete-user',
    description: 'Delete a user and all corresponding files',
)]
class DeleteUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManagementService  $userManager
    )
    {
        parent::__construct('delete-user');
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::REQUIRED, 'Username must be unique');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($user === null) {
            $io->block(
                "User deletion failed.\nError: User $username was not found.}",
                null,
                'fg=white;bg=red;',
                ' ',
                true
            );
            return self::FAILURE;
        }

        // Before deleting the user prompt a confirmation message
        if (!$io->confirm("Are you sure you want to delete user $username? All user files will be deleted, action is not reversible")) {
            $io->warning("Operation cancelled.");
            return self::SUCCESS;
        }

        $this->userManager->delete($user);

        $io->block(
            "User $username and all corresponding files in /srv/storage/$username were deleted.",
            null,
            'fg=white;bg=green;',
            ' ',
            true
        );
        return self::SUCCESS;
    }
}
