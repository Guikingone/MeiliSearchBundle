<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use Generator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use function sprintf;

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
        static::assertTrue($command->getDefinition()->hasArgument('index'));
        static::assertTrue($command->getDefinition()->getArgument('index')->isRequired());
        static::assertSame('Allow to delete an index', $command->getDescription());
    }

    /**
     * @dataProvider provideIndexes
     */
    public function testCommandCannotDeleteInvalidIndex(string $index): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([
            'index' => $index,
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString(
            '[ERROR] An error occurred when trying to delete the index',
            $tester->getDisplay()
        );
        static::assertStringContainsString('Error: An error occurred', $tester->getDisplay());
    }

    /**
     * @dataProvider provideIndexes
     */
    public function testCommandCanDeleteValidIndexWithoutConfirmation(string $index): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('removeIndex');

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => $index,
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('The index has not been deleted', $tester->getDisplay());
    }

    /**
     * @dataProvider provideIndexes
     */
    public function testCommandCanDeleteValidIndex(string $index): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->with(self::equalTo($index));

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([
            'index' => $index,
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(sprintf('The index "%s" has been removed', $index), $tester->getDisplay());
    }

    /**
     * @dataProvider provideIndexes
     */
    public function testCommandCanDeleteValidIndexWithForceOption(string $index): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex')->with(self::equalTo($index));

        $command = new DeleteIndexCommand($orchestrator);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => $index,
            '--force' => true,
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(sprintf('The index "%s" has been removed', $index), $tester->getDisplay());
    }

    public function provideIndexes(): Generator
    {
        yield ['foo', 'bar', 'random'];
    }
}
