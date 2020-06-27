<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Client;

use MeiliSearch\Client;
use MeiliSearch\Index;
use MeiliSearchBundle\Client\DocumentOrchestrator;
use MeiliSearchBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentOrchestratorTest extends TestCase
{
    public function testDocumentCannotBeReturnedWithInvalidIndex(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Index::class);
        $index->expects(self::once())->method('getDocument')->willThrowException(new \Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new DocumentOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        $orchestrator->getDocument('test', 'test');
    }

    public function testDocumentCanBeReturned(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Index::class);
        $index->expects(self::once())->method('getDocument')->willReturn([
            'id' => 'foo',
            'value' => 'foo',
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new DocumentOrchestrator($client);
        $document = $orchestrator->getDocument('test', 'test');

        static::assertArrayHasKey('id', $document);
    }

    public function testDocumentsCannotBeReturnedWithInvalidIndex(): void
    {
    }

    public function testDocumentsCanBeReturned(): void
    {
    }

    public function testDocumentCannotBeUpdatedWithInvalidIndex(): void
    {
    }

    public function testDocumentCanBeUpdated(): void
    {
    }

    public function testDocumentCannotBeRemovedWithInvalidIndex(): void
    {
    }

    public function testDocumentCanBeRemoved(): void
    {
    }

    public function testSetOfDocumentsCannotBeRemovedWithInvalidIndex(): void
    {
    }

    public function testSetOfDocumentsCanBeRemoved(): void
    {
    }

    public function testDocumentsCannotBeRemovedWithInvalidIndex(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $index = $this->createMock(Index::class);
        $index->expects(self::once())->method('deleteAllDocuments')->willThrowException(new \Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new DocumentOrchestrator($client, null, $logger);

        static::expectException(RuntimeException::class);
        $orchestrator->removeDocuments('test');
    }

    public function testDocumentsCanBeRemoved(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Index::class);
        $index->expects(self::once())->method('deleteAllDocuments')->willReturn(['updateId' => 1]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new DocumentOrchestrator($client, null, $logger);
        $orchestrator->removeDocuments('test');
    }
}
