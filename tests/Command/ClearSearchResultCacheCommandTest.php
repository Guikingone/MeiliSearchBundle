<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Command\ClearSearchResultCacheCommand;
use MeiliSearchBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ClearSearchResultCacheCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $searchResultCacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);

        $command = new ClearSearchResultCacheCommand($searchResultCacheOrchestrator);

        static::assertSame('meili:clear-search-cache', $command->getName());
        static::assertSame(
            'Allow to clear the cache used by the CachedSearchResultEntryPoint',
            $command->getDescription()
        );
    }

    public function testCommandCannotClearOnError(): void
    {
        $searchResultCacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $searchResultCacheOrchestrator->expects(self::once())->method('clear')->willThrowException(
            new RuntimeException('The cache pool cannot be cleared')
        );

        $command = new ClearSearchResultCacheCommand($searchResultCacheOrchestrator);

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('The cache pool cannot be cleared', $tester->getDisplay());
    }

    public function testCommandCanClear(): void
    {
        $searchResultCacheOrchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $searchResultCacheOrchestrator->expects(self::once())->method('clear');

        $command = new ClearSearchResultCacheCommand($searchResultCacheOrchestrator);

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The cache pool has been cleared', $tester->getDisplay());
    }
}
