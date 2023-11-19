<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Update;

use Exception;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Update\Update;
use MeiliSearchBundle\Update\UpdateInterface;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateOrchestratorTest extends TestCase
{
    public function testUpdateCannotBeRetrievedWithException(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new UpdateOrchestrator($client);

        static::expectException(Throwable::class);
        $orchestrator->getUpdate('foo', 1);
    }

    public function testUpdateCannotBeRetrievedWithExceptionAndLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when trying to fetch the index, error "An error occurred"')
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new UpdateOrchestrator($client, $logger);

        static::expectException(Throwable::class);
        $orchestrator->getUpdate('foo', 1);
    }

    public function testUpdateCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getTask')->willReturn([
            'uid' => 1,
            'indexUid' => 'movies',
            'status' => 'processed',
            'type' => 'documentAdditionOrUpdate',
            'canceledBy' => null,
            'details' => [
                'rankingRules' => [
                    'typo',
                    'ranking:desc',
                    'words',
                    'proximity',
                    'attribute',
                    'exactness',
                ],
            ],
            'error' => null,
            'duration' => 'PT0.000400211S',
            'enqueuedAt' => '2023-11-11T22:00:00.00000003Z',
            'startedAt' => '2023-11-11T22:00:01.00000003Z',
            'finishedAt' => '2023-11-11T22:00:02.00000003Z',
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('An update has been retrieved'),
            ['index' => 'foo']
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new UpdateOrchestrator($client, $logger);
        $update = $orchestrator->getUpdate('foo', 1);

        static::assertInstanceOf(Update::class, $update);
    }

    public function testUpdatesCannotBeRetrievedWithException(): void
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new UpdateOrchestrator($client);

        static::expectException(Throwable::class);
        $orchestrator->getUpdates('foo');
    }

    public function testUpdatesCannotBeRetrievedWithExceptionAndLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when trying to fetch the index, error "An error occurred"')
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new UpdateOrchestrator($client, $logger);

        static::expectException(Throwable::class);
        $orchestrator->getUpdates('foo');
    }

    public function testUpdatesCannotBeRetrievedWhenEmptyWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getTasks')->willReturn(new TasksResults(['results' => []]));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new UpdateOrchestrator($client);
        $updates = $orchestrator->getUpdates('foo');

        static::assertEmpty($updates);
    }

    public function testUpdatesCannotBeRetrievedWhenEmptyWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('A set of updates has been retrieved'),
            ['index' => 'foo']
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getTasks')->willReturn(new TasksResults(['results' => []]));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new UpdateOrchestrator($client, $logger);
        $updates = $orchestrator->getUpdates('foo');

        static::assertEmpty($updates);
    }

    public function testUpdatesCanBeRetrievedWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getTasks')->willReturn(
            new TasksResults([
                'results' => [
                    [
                        'uid' => 1,
                        'indexUid' => 'movies',
                        'status' => 'processed',
                        'type' => 'documentAdditionOrUpdate',
                        'canceledBy' => null,
                        'details' => [
                            'receivedDocuments' => 1,
                            'indexedDocuments' => 2,
                        ],
                        'error' => null,
                        'duration' => 'PT0.000400211S',
                        'enqueuedAt' => '2023-11-11T22:00:00.00000003Z',
                        'startedAt' => '2023-11-11T22:00:01.00000003Z',
                        'finishedAt' => '2023-11-11T22:00:02.00000003Z',
                    ],
                    [
                        'uid' => 2,
                        'indexUid' => 'movies',
                        'status' => 'processed',
                        'type' => 'documentAdditionOrUpdate',
                        'canceledBy' => null,
                        'details' => [
                            'receivedDocuments' => 1,
                            'indexedDocuments' => 2,
                        ],
                        'error' => null,
                        'duration' => 'PT0.000400211S',
                        'enqueuedAt' => '2023-11-12T22:00:00.00000003Z',
                        'startedAt' => '2023-11-12T22:00:01.00000003Z',
                        'finishedAt' => '2023-11-12T22:00:02.00000003Z',
                    ],
                ],
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new UpdateOrchestrator($client);
        $updates = $orchestrator->getUpdates('foo');

        static::assertNotEmpty($updates);
        static::assertInstanceOf(UpdateInterface::class, $updates[0]);
        static::assertInstanceOf(UpdateInterface::class, $updates[1]);
    }

    public function testUpdatesCanBeRetrievedWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('A set of updates has been retrieved'),
            self::equalTo(['index' => 'foo'])
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getTasks')->willReturn(
            new TasksResults([
                'results' => [
                    [
                        'uid' => 1,
                        'indexUid' => 'movies',
                        'status' => 'processed',
                        'type' => 'documentAdditionOrUpdate',
                        'canceledBy' => null,
                        'details' => [
                            'receivedDocuments' => 1,
                            'indexedDocuments' => 2,
                        ],
                        'error' => null,
                        'duration' => 'PT0.000400211S',
                        'enqueuedAt' => '2023-11-11T22:00:00.00000003Z',
                        'startedAt' => '2023-11-11T22:00:01.00000003Z',
                        'finishedAt' => '2023-11-11T22:00:02.00000003Z',
                    ],
                    [
                        'uid' => 2,
                        'indexUid' => 'movies',
                        'status' => 'processed',
                        'type' => 'documentAdditionOrUpdate',
                        'canceledBy' => null,
                        'details' => [
                            'receivedDocuments' => 1,
                            'indexedDocuments' => 2,
                        ],
                        'error' => null,
                        'duration' => 'PT0.000400211S',
                        'enqueuedAt' => '2023-11-12T22:00:00.00000003Z',
                        'startedAt' => '2023-11-12T22:00:01.00000003Z',
                        'finishedAt' => '2023-11-12T22:00:02.00000003Z',
                    ],
                ],
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new UpdateOrchestrator($client, $logger);
        $updates = $orchestrator->getUpdates('foo');

        static::assertNotEmpty($updates);
        static::assertInstanceOf(UpdateInterface::class, $updates[0]);
        static::assertInstanceOf(UpdateInterface::class, $updates[1]);
    }
}
