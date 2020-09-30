<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Command\DeleteIndexesCommand;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new DeleteIndexesCommand($orchestrator, new IndexMetadataRegistry());

        static::assertSame('meili:delete-indexes', $command->getName());
        static::assertSame('Delete every indexes stored in MeiliSearch', $command->getDescription());
    }

    public function testCommandCannotClearWithoutConfirmationAnswer(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('removeIndexes');

        $command = new DeleteIndexesCommand($orchestrator, new IndexMetadataRegistry());

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithoutConfirmation(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('removeIndexes');

        $command = new DeleteIndexesCommand($orchestrator, new IndexMetadataRegistry());

        $tester = new CommandTester($command);
        $tester->setInputs(['no']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithError(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndexes')->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexesCommand($orchestrator, new IndexMetadataRegistry());

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('An error occurred when trying to removed all the indexes', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanClear(): void
    {
        $registry = new IndexMetadataRegistry();
        $registry->add('foo', new IndexMetadata('foo'));

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
