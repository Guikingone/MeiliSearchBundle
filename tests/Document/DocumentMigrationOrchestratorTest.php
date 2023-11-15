<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\DocumentMigrationOrchestrator;
use MeiliSearchBundle\Dump\DumpOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentMigrationOrchestratorTest extends TestCase
{
    public function testOrchestratorCannotMigrateWithEmptyDocuments(): void
    {
        $dumpOrchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $dumpOrchestrator->expects(self::never())->method('create');

        $entryPoint = $this->createMock(DocumentEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('getDocuments')->with(self::equalTo('foo'))->willReturn([]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('The documents from "foo" cannot be migrated as the document list is empty')
        );

        $orchestrator = new DocumentMigrationOrchestrator($entryPoint, $dumpOrchestrator, $logger);
        $orchestrator->migrate('foo', 'bar');
    }

    public function testOrchestratorCannotMigrateWithDocumentsWithException(): void
    {
        $dumpOrchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $dumpOrchestrator->expects(self::once())->method('create');

        $entryPoint = $this->createMock(DocumentEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('getDocuments')->with(self::equalTo('foo'))->willReturn([
            [
                'id' => 1,
                'title' => 'foo',
            ],
            [
                'id' => 2,
                'title' => 'bar',
            ],
        ]);
        $entryPoint->expects(self::once())->method('addDocuments')->with(
            self::equalTo('bar'),
            self::equalTo([
                [
                    'id' => 1,
                    'title' => 'foo',
                ],
                [
                    'id' => 2,
                    'title' => 'bar',
                ],
            ])
        )->willThrowException(new RuntimeException('An error occurred'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');
        $logger->expects(self::once())->method('critical')->with(
            self::equalTo(
                'The documents cannot be migrated, a dump has been created before trying to add the new documents'
            ),
            self::equalTo([
                'error' => 'An error occurred',
                'index' => 'bar',
            ])
        );

        $orchestrator = new DocumentMigrationOrchestrator($entryPoint, $dumpOrchestrator, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionCode(0);
        static::expectExceptionMessage('An error occurred');
        $orchestrator->migrate('foo', 'bar');
    }

    public function testOrchestratorCanMigrate(): void
    {
        $dumpOrchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $dumpOrchestrator->expects(self::once())->method('create');

        $entryPoint = $this->createMock(DocumentEntryPointInterface::class);
        $entryPoint->expects(self::never())->method('removeDocuments');
        $entryPoint->expects(self::once())->method('getDocuments')->with(self::equalTo('foo'))->willReturn([
            [
                'id' => 1,
                'title' => 'foo',
            ],
            [
                'id' => 2,
                'title' => 'bar',
            ],
        ]);
        $entryPoint->expects(self::once())->method('addDocuments')->with(
            self::equalTo('bar'),
            self::equalTo([
                [
                    'id' => 1,
                    'title' => 'foo',
                ],
                [
                    'id' => 2,
                    'title' => 'bar',
                ],
            ])
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('The documents have been migrated'),
            self::equalTo([
                'index' => 'foo',
                'nextIndex' => 'bar',
            ])
        );

        $orchestrator = new DocumentMigrationOrchestrator($entryPoint, $dumpOrchestrator, $logger);
        $orchestrator->migrate('foo', 'bar');
    }

    public function testOrchestratorCanMigrateWithRemoveOldIndexDocuments(): void
    {
        $dumpOrchestrator = $this->createMock(DumpOrchestratorInterface::class);
        $dumpOrchestrator->expects(self::once())->method('create');

        $entryPoint = $this->createMock(DocumentEntryPointInterface::class);
        $entryPoint->expects(self::once())->method('removeDocuments')->with(self::equalTo('foo'));
        $entryPoint->expects(self::once())->method('getDocuments')->with(self::equalTo('foo'))->willReturn([
            [
                'id' => 1,
                'title' => 'foo',
            ],
            [
                'id' => 2,
                'title' => 'bar',
            ],
        ]);
        $entryPoint->expects(self::once())->method('addDocuments')->with(
            self::equalTo('bar'),
            self::equalTo([
                [
                    'id' => 1,
                    'title' => 'foo',
                ],
                [
                    'id' => 2,
                    'title' => 'bar',
                ],
            ])
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('critical');
        $logger->expects(self::once())->method('info')->with(
            self::equalTo('The documents have been migrated'),
            self::equalTo([
                'index' => 'foo',
                'nextIndex' => 'bar',
            ])
        );

        $orchestrator = new DocumentMigrationOrchestrator($entryPoint, $dumpOrchestrator, $logger);
        $orchestrator->migrate('foo', 'bar', true);
    }
}
