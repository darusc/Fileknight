<?php

namespace Fileknight\Scheduler\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Fileknight\Entity\RefreshToken;
use Fileknight\Repository\RefreshTokenRepository;
use Fileknight\Scheduler\Message\CleanupExpiredRefreshTokens;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CleanupExpiredRefreshTokensHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(CleanupExpiredRefreshTokens $message): void
    {
        /** @var RefreshTokenRepository $repository */
        $repository = $this->entityManager->getRepository(RefreshToken::class);
        $rows = $repository->deleteExpired();

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        echo "[Scheduler@$now] CleanupExpiredRefreshTokens. Deleted $rows tokens.\n";
    }
}
