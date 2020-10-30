<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Command\DeleteIndexesCommand;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new DeleteIndexesCommand($orchestrator, $registry);

        static::assertSame('meili:delete-indexes', $command->getName());
        static::assertSame('Delete every indexes stored in MeiliSearch', $command->getDescription());
    }

    public function testCommandCannotClearWithoutConfirmationAnswer(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::never())->method('clear');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('removeIndexes');

        $command = new DeleteIndexesCommand($orchestrator, $registry);

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithoutConfirmation(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::never())->method('clear');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('removeIndexes');

        $command = new DeleteIndexesCommand($orchestrator, $registry);

        $tester = new CommandTester($command);
        $tester->setInputs(['no']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithError(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::never())->method('clear');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndexes')->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexesCommand($orchestrator, $registry);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('An error occurred when trying to removed all the indexes', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanClear(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('clear');

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndexes');

        $command = new DeleteIndexesCommand($orchestrator, $registry);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertEmpty($registry->toArray());
        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('All the indexes have been removed', $tester->getDisplay());
    }
}
