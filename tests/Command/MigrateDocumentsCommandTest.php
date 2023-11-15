<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearchBundle\Command\MigrateDocumentsCommand;
use MeiliSearchBundle\Document\DocumentMigrationOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MigrateDocumentsCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $orchestrator = $this->createMock(DocumentMigrationOrchestratorInterface::class);

        $command = new MigrateDocumentsCommand($orchestrator);

        static::assertSame('meili:migrate-documents', $command->getName());
        static::assertNotEmpty($command->getDefinition());
        static::assertTrue($command->getDefinition()->hasArgument('oldIndex'));
        static::assertTrue($command->getDefinition()->getArgument('oldIndex')->isRequired());
        static::assertSame(
            'The name of the index from the documents must be migrated',
            $command->getDefinition()->getArgument('oldIndex')->getDescription()
        );
        static::assertTrue($command->getDefinition()->hasOption('index'));
        static::assertSame('i', $command->getDefinition()->getOption('index')->getShortcut());
        static::assertTrue($command->getDefinition()->getOption('index')->isValueRequired());
        static::assertSame(
            'The name of the index where the documents must be migrated',
            $command->getDefinition()->getOption('index')->getDescription()
        );
        static::assertTrue($command->getDefinition()->hasOption('remove'));
        static::assertSame('r', $command->getDefinition()->getOption('remove')->getShortcut());
        static::assertFalse($command->getDefinition()->getOption('remove')->isValueRequired());
        static::assertSame(
            'If the documents must be removed from the old index',
            $command->getDefinition()->getOption('remove')->getDescription()
        );
    }

    public function testCommandCannotMigrateWithException(): void
    {
        $orchestrator = $this->createMock(DocumentMigrationOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('migrate')
            ->with(self::equalTo('foo'), self::equalTo('bar'), self::equalTo(false))
            ->willThrowException(new RuntimeException('An error occurred'));

        $command = new MigrateDocumentsCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([
            'oldIndex' => 'foo',
            '--index' => 'bar',
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[ERROR] The documents cannot be migrated!', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanMigrate(): void
    {
        $orchestrator = $this->createMock(DocumentMigrationOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('migrate')->with(
            self::equalTo('foo'),
            self::equalTo('bar'),
            self::equalTo(false)
        );

        $command = new MigrateDocumentsCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([
            'oldIndex' => 'foo',
            '--index' => 'bar',
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('[OK] The documents have been migrated', $tester->getDisplay());
    }

    public function testCommandCanMigrateWithRemove(): void
    {
        $orchestrator = $this->createMock(DocumentMigrationOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('migrate')->with(
            self::equalTo('foo'),
            self::equalTo('bar'),
            self::equalTo(true)
        );

        $command = new MigrateDocumentsCommand($orchestrator);
        $tester = new CommandTester($command);
        $tester->execute([
            'oldIndex' => 'foo',
            '--index' => 'bar',
            '--remove' => true,
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('[OK] The documents have been migrated', $tester->getDisplay());
    }
}
