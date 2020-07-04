<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearch\Client;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Command\ListIndexesCommand;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ListIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $client = $this->createMock(Client::class);
        $orchestrator = new IndexOrchestrator($client);

        $command = new ListIndexesCommand($orchestrator);

        static::assertSame('meili:list-indexes', $command->getName());
        static::assertSame('List the indexes', $command->getDescription());
    }

    public function testCommandCannotListIndexesWithException(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willThrowException(new RuntimeException('An error occurred'));

        $orchestrator = new IndexOrchestrator($client);

        $command = new ListIndexesCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString(
            '[ERROR] The list cannot be retrieved as an error occurred',
            $tester->getDisplay()
        );
        static::assertStringContainsString('Error: An error occurred', $tester->getDisplay());
    }

    public function testCommandCannotListEmptyIndexes(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willReturn([]);

        $orchestrator = new IndexOrchestrator($client);

        $command = new ListIndexesCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            'No indexes found, please ensure that indexes have been created',
            $tester->getDisplay()
        );
    }

    public function testCommandCanListIndexes(): void
    {
        $firstIndex = $this->createMock(Indexes::class);
        $firstIndex->expects(self::once())->method('show')->willReturn([
            'uid' => 'movies',
            'primaryKey' => 'movie_id',
            'createdAt' => '2019-11-20T09:40:33.711324Z',
            'updatedAt' => '2019-11-20T10:16:42.761858Z',
        ]);
        $secondIndex = $this->createMock(Indexes::class);
        $secondIndex->expects(self::once())->method('show')->willReturn([
            'uid' => 'movie_reviews',
            'primaryKey' => null,
            'createdAt' => '2019-11-20T09:40:33.711324Z',
            'updatedAt' => '2019-11-20T10:16:42.761858Z',
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getAllIndexes')->willReturn([
            $firstIndex,
            $secondIndex
        ]);

        $orchestrator = new IndexOrchestrator($client);

        $command = new ListIndexesCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The following indexes have been found:', $tester->getDisplay());
        static::assertStringContainsString('Uid', $tester->getDisplay());
        static::assertStringContainsString('PrimaryKey', $tester->getDisplay());
        static::assertStringContainsString('CreatedAt', $tester->getDisplay());
        static::assertStringContainsString('UpdatedAt', $tester->getDisplay());

        static::assertStringContainsString('movies', $tester->getDisplay());
        static::assertStringContainsString('movie_id', $tester->getDisplay());
        static::assertStringContainsString('2019-11-20T09:40:33.711324Z', $tester->getDisplay());
        static::assertStringContainsString('2019-11-20T10:16:42.761858Z', $tester->getDisplay());

        static::assertStringContainsString('movie_reviews', $tester->getDisplay());
        static::assertStringContainsString('Undefined', $tester->getDisplay());
        static::assertStringContainsString('2019-11-20T09:40:33.711324Z', $tester->getDisplay());
        static::assertStringContainsString('2019-11-20T10:16:42.761858Z', $tester->getDisplay());
    }
}
