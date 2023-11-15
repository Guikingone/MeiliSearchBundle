<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Command;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsCommand(
    name: 'meili:clear-search-cache',
    description: 'Allow to clear the cache used by the CachedSearchResultEntryPoint',
)]
final class ClearSearchResultCacheCommand extends Command
{
    public function __construct(private readonly SearchResultCacheOrchestratorInterface $searchResultCacheOrchestrator)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->searchResultCacheOrchestrator->clear();
        } catch (Throwable $throwable) {
            $io->error($throwable->getMessage());

            return 1;
        }

        $io->success('The cache pool has been cleared');

        return 0;
    }
}
