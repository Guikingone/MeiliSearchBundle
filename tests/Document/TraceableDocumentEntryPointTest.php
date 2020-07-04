<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDocumentEntryPointTest extends TestCase
{
    public function testDocumentCanBeAdded(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->addDocument('foo', [
            'id' => 1,
            'title' => 'bar',
        ]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['addedDocuments']);
    }

    public function testDocumentCanBeAddedWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator, $logger);
        $traceableDocumentOrchestrator->addDocument('foo', [
            'id' => 1,
            'title' => 'bar',
        ]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['addedDocuments']);
    }

    public function testDocumentCanBeRetrieved(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('getDocument')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->getDocument('foo', 1);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['retrievedDocuments']);
    }

    public function testDocumentCanBeRetrievedWithLogger(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('getDocument')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->getDocument('foo', 1);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['retrievedDocuments']);
        static::assertArrayHasKey('foo', $traceableDocumentOrchestrator->getData()['retrievedDocuments']);
        static::assertCount(1, $traceableDocumentOrchestrator->getData()['retrievedDocuments']['foo']);
        static::assertSame(1, $traceableDocumentOrchestrator->getData()['retrievedDocuments']['foo'][0]['id']);
        static::assertArrayHasKey('document', $traceableDocumentOrchestrator->getData()['retrievedDocuments']['foo'][0]);
    }

    public function testASetOfDocumentCanBeRetrieved(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('getDocuments')->willReturn([
            [
                'id' => 1,
                'title' => 'bar',
            ],
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->getDocuments('foo');

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['retrievedDocuments']);
    }

    public function testASetOfDocumentCanBeRetrievedWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('getDocuments')->willReturn([
            [
                'id' => 1,
                'title' => 'bar',
            ],
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ]);

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator, $logger);
        $traceableDocumentOrchestrator->getDocuments('foo');

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['retrievedDocuments']);
    }

    public function testASetOfDocumentCanBeUpdated(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('updateDocument');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->updateDocument('foo', [
            'id' => 1,
            'title' => 'bar',
        ]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['updatedDocuments']);
    }

    public function testASetOfDocumentCanBeUpdatedWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('updateDocument');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator, $logger);
        $traceableDocumentOrchestrator->updateDocument('foo', [
            'id' => 1,
            'title' => 'bar',
        ]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['updatedDocuments']);
    }

    public function testADocumentCanBeRemoved(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->removeDocument('foo', 1);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['removedDocuments']);
    }

    public function testASetOfDocumentsCanBeRemoved(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeSetOfDocuments');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->removeSetOfDocuments('foo', [1, 2, 3]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['removedDocuments']);
    }

    public function testDocumentsCanBeRemoved(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeSetOfDocuments');

        $traceableDocumentOrchestrator = new TraceableDocumentEntryPoint($orchestrator);
        $traceableDocumentOrchestrator->removeSetOfDocuments('foo', [1, 2, 3]);

        static::assertNotEmpty($traceableDocumentOrchestrator->getData()['removedDocuments']);
    }
}
