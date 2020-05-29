<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Tests\Command;

use MeiliSearchBundle\Client\ClientInterface;
use MeiliSearchBundle\Command\ListIndexesCommand;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ListIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $command = new ListIndexesCommand($client);

        static::assertSame('meili:list-indexes', $command->getName());
    }

    public function testCommandCannotListIndexesWithException(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('getIndexes')->willThrowException(new RuntimeException('An error occurred'));

        $command = new ListIndexesCommand($client);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[ERROR] The list cannot be retrieved as an error occurred, message: "An error occurred".', $tester->getDisplay());
    }

    public function testCommandCannotListEmptyIndexes(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('getIndexes')->willReturn([]);

        $command = new ListIndexesCommand($client);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('No indexes found, please ensure that indexes have been created', $tester->getDisplay());
    }

    public function testCommandCanListIndexes(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('getIndexes')->willReturn([
            [
                "uid" => "movies",
                "primaryKey" => "movie_id",
                "createdAt" => "2019-11-20T09:40:33.711324Z",
                "updatedAt" => "2019-11-20T10:16:42.761858Z",
            ],
            [
                "uid" => "movie_reviews",
                "primaryKey" => null,
                "createdAt" => "2019-11-20T09:40:33.711324Z",
                "updatedAt" => "2019-11-20T10:16:42.761858Z"
            ],
        ]);

        $command = new ListIndexesCommand($client);
        $application = new Application();
        $application->add($command);
        $tester = new CommandTester($application->get('meili:list-indexes'));
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
