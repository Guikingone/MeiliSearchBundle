<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Document;

use ArrayAccess;
use Exception;
use Generator;
use InvalidArgumentException;
use Meilisearch\Client;
use Meilisearch\Contracts\DocumentsQuery;
use Meilisearch\Contracts\DocumentsResults;
use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Event\Document\PostDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PostDocumentRetrievedEvent;
use MeiliSearchBundle\Event\Document\PreDocumentDeletionEvent;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Result\ResultBuilder;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function implode;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentEntryPointTest extends TestCase
{
    public function testDocumentCannotBeAddedWithInvalidIndex(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            'The document cannot be created, error: "An error occurred"'
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('addDocuments');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->addDocument('test', [
            'id' => 1,
            'title' => 'foo',
        ]);
    }

    public function testDocumentCannotBeAddedWithInvalidDocument(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(1))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            'The document cannot be created, error: "An error occurred"'
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('addDocuments')->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->addDocument('test', [
            'id' => 1,
            'title' => 'foo',
        ]);
    }

    public function testDocumentCanBeAdded(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('addDocuments')->with([
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ], null)->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);
        $orchestrator->addDocument('test', [
            'id' => 1,
            'title' => 'foo',
        ]);
    }

    public function testDocumentCanBeAddedWithModel(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('addDocuments')->with([
            [
                'id' => 1,
                'title' => 'foo',
                'model' => stdClass::class,
            ],
        ], 'id')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);
        $orchestrator->addDocument('test', [
            'id' => 1,
            'title' => 'foo',
        ], 'id', stdClass::class);
    }

    public function testDocumentsCannotBeAddedWithInvalidIndex(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            'The document cannot be created, error: "An error occurred"'
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('addDocuments');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->addDocuments('test', [
            'id' => 1,
            'title' => 'foo',
        ]);
    }

    public function testDocumentsCannotBeAddedWithInvalidBody(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(1))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            'The document cannot be created, error: "An error occurred"'
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('addDocuments')->with([
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ])->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->addDocuments('test', [
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);
    }

    public function testDocumentsCanBeAdded(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('addDocuments')->with([
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ], null)->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);
        $orchestrator->addDocuments('test', [
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);
    }

    public function testDocumentCannotBeReturnedWithInvalidIndex(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getDocument');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->getDocument('test', 'test');
    }

    public function testDocumentCannotBeReturnedWithInvalidDocument(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocument')->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->getDocument('test', 'test');
    }

    public function testDocumentCanBeReturned(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocument')->willReturn([
            'id' => 'foo',
            'value' => 'foo',
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $matcher = $this->exactly(2);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch')
            ->willReturnCallback(function () use ($matcher, $index) {
                return match ($matcher->getInvocationCount()) {
                    0 => new PreDocumentRetrievedEvent($index, 'test'),
                    default => new PostDocumentRetrievedEvent($index, [
                        'id' => 'foo',
                        'value' => 'foo',
                    ])
                };
            });

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher);
        /** @var array{mixed}|ArrayAccess $document */
        $document = $orchestrator->getDocument('test', 'test');

        static::assertArrayHasKey('id', $document);
    }

    public function testDocumentCanBeReturnedWithModel(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);
        $resultBuilder->expects(self::once())->method('build')->willReturn(FooModel::create(1, 'foo'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocument')->willReturn([
            'id' => 1,
            'value' => 'foo',
            'model' => FooModel::class,
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);
        $document = $orchestrator->getDocument('test', 'test');

        static::assertInstanceOf(FooModel::class, $document);
    }

    public function testDocumentsCannotBeReturnedWithInvalidIndex(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The documents cannot be retrieved, error: "An error occurred"')
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getDocuments');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->getDocuments('test');
    }

    public function testDocumentsCannotBeReturnedWithInvalidOptions(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('getDocuments');

        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('index');

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);

        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('The option "test" is not a valid one.');
        static::expectExceptionCode(0);
        $orchestrator->getDocuments('test', ['test' => 'test']);
    }

    /**
     * @dataProvider provideOptions
     */
    public function testDocumentsCanBeReturnedWithValidOption(array $options): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocuments')->with(self::equalTo($options[1]))->willReturn(
            new DocumentsResults([
                'results' => [
                    [
                        'id' => 1,
                        'value' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'value' => 'foo',
                    ],
                ],
                'offset' => 1,
                'limit' => 20,
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);
        static::assertNotEmpty($orchestrator->getDocuments('test', $options[0]));
    }

    public function testDocumentsCanBeReturned(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocuments')->willReturn(
            new DocumentsResults([
                'results' => [
                    [
                        'id' => 1,
                        'value' => 'foo',
                    ],
                    [
                        'id' => 2,
                        'value' => 'foo',
                    ],
                ],
                'offset' => 0,
                'limit' => 20,
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);

        static::assertNotEmpty($orchestrator->getDocuments('test'));
    }

    public function testDocumentsCanBeReturnedWithModels(): void
    {
        $resultBuilder = new ResultBuilder(new Serializer([new ObjectNormalizer()]));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocuments')->willReturn(
            new DocumentsResults([
                'results' => [
                    [
                        'id' => 1,
                        'value' => 'foo',
                        'model' => FooModel::class,
                    ],
                    [
                        'id' => 2,
                        'value' => 'foo',
                        'model' => FooModel::class,
                    ],
                ],
                'offset' => 0,
                'limit' => 20,
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);
        $documents = $orchestrator->getDocuments('test');

        static::assertNotEmpty($documents);
        static::assertInstanceOf(FooModel::class, $documents[0]);
        static::assertInstanceOf(FooModel::class, $documents[1]);
    }

    /**
     * @throws Exception
     */
    public function testDocumentsCanBeReturnedWithModelsThatUseUuid(): void
    {
        $resultBuilder = new ResultBuilder(
            new Serializer([
                new ObjectNormalizer(),
                new UuidNormalizer(),
                new UuidDenormalizer(),
            ])
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocuments')->willReturn(
            new DocumentsResults([
                'results' => [
                    [
                        'id' => (Uuid::uuid4())->toString(),
                        'value' => 'foo',
                        'model' => BarModel::class,
                    ],
                    [
                        'id' => (Uuid::uuid4())->toString(),
                        'value' => 'foo',
                        'model' => BarModel::class,
                    ],
                ],
                'offset' => 0,
                'limit' => 20,
            ])
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);
        $documents = $orchestrator->getDocuments('test');

        static::assertNotEmpty($documents);
        static::assertInstanceOf(BarModel::class, $documents[0]);
        static::assertNotNull($documents[0]->getId());
        static::assertInstanceOf(BarModel::class, $documents[1]);
        static::assertNotNull($documents[1]->getId());
    }

    public function testDocumentCannotBeUpdatedWithInvalidIndex(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('updateDocuments')->with([
            [
                'id' => 1,
                'value' => 'foo',
            ],
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionCode(0);
        static::expectExceptionMessage('An error occurred');
        $orchestrator->updateDocument('test', [
            'id' => 1,
            'value' => 'foo',
        ]);
    }

    public function testDocumentCanBeUpdatedWithLogger(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateDocuments')->with([
            [
                'id' => 1,
                'value' => 'foo',
            ],
        ])->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);
        $orchestrator->updateDocument('test', [
            'id' => 1,
            'value' => 'foo',
        ]);
    }

    public function testDocumentCanBeUpdated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateDocuments')->with([
            [
                'id' => 1,
                'value' => 'foo',
            ],
        ])->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher);
        $orchestrator->updateDocument('test', [
            'id' => 1,
            'value' => 'foo',
        ]);
    }

    public function testDocumentCannotBeRemovedWithInvalidIndex(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The document cannot be removed, error: "An error occurred"')
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('deleteDocument')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->removeDocument('foo', 1);
    }

    public function testDocumentCanBeRemoved(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getDocument')->willReturn([
            'id' => 1,
            'value' => 'foo',
        ]);
        $index->expects(self::once())->method('deleteDocument')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $matcher = $this->exactly(2);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch')
            ->willReturnCallback(function () use ($matcher, $index) {
                return match ($matcher->getInvocationCount()) {
                    0 => new PreDocumentDeletionEvent($index, [
                        'id' => 1,
                        'value' => 'foo',
                    ]),
                    default => new PostDocumentDeletionEvent(1)
                };
            });

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher);
        $orchestrator->removeDocument('foo', 1);
    }

    public function testSetOfDocumentsCannotBeRemovedWithInvalidIndex(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info')->with(self::equalTo('A set of documents has been removed'), [
            'documents' => implode(', ', [1, 2, 3]),
            'task_uid' => 1,
        ]);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The documents cannot be removed, error: "An error occurred"')
        );

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('deleteDocuments')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->removeSetOfDocuments('foo', [1, 2, 3]);
    }

    public function testSetOfDocumentsCanBeRemovedWithLogger(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('A set of documents has been removed'), [
            'documents' => implode(', ', [1, 2, 3]),
            'task_uid' => 1,
        ]);

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('deleteDocuments')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, $eventDispatcher, $logger);
        $orchestrator->removeSetOfDocuments('foo', [1, 2, 3]);
    }

    public function testSetOfDocumentsCanBeRemoved(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('deleteDocuments')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder);
        $orchestrator->removeSetOfDocuments('foo', [1, 2, 3]);
    }

    public function testDocumentsCannotBeRemovedWithInvalidIndex(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('deleteAllDocuments')->willThrowException(
            new Exception('An error occurred')
        );

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $orchestrator->removeDocuments('test');
    }

    public function testDocumentsCanBeRemoved(): void
    {
        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('deleteAllDocuments')->willReturn(['taskUid' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('index')->willReturn($index);

        $orchestrator = new DocumentEntryPoint($client, $resultBuilder, null, $logger);
        $orchestrator->removeDocuments('test');
    }

    public function provideOptions(): Generator
    {
        yield 'offset' => [
            [
                [
                    'offset' => 1,
                ],
                (new DocumentsQuery())->setOffset(1),
            ],
        ];
        yield 'limit' => [
            [
                [
                    'limit' => 1,
                ],
                (new DocumentsQuery())->setLimit(1),
            ],
        ];
        yield 'fields' => [
            [
                [
                    'fields' => ['id', 'title'],
                ],
                (new DocumentsQuery())->setFields(['id', 'title']),
            ],
        ];
    }
}

final class FooModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public static function create(int $id, string $title): FooModel
    {
        $self = new self();

        $self->id = $id;
        $self->title = $title;

        return $self;
    }
}

final class BarModel
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public static function create(string $id, string $title): BarModel
    {
        $self = new self();

        $self->id = $id;
        $self->title = $title;

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
