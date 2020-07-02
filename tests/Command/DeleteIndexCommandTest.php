<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new DeleteIndexCommand($orchestrator);

        static::assertSame('meili:delete-index', $command->getName());
    }

    public function testCommandCannotDeleteInvalidIndex(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => 'foo',
        ]);

        static::assertStringContainsString('An error occurred when trying to delete the index, error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanDeleteValidIndex(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => 'foo',
        ]);

        static::assertStringContainsString('The index "foo" has been deleted', $tester->getDisplay());
    }
}
