<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use Generator;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Index\IndexSynchronizerInterface;
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
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new DeleteIndexCommand($synchronizer);

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
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('dropIndex')
            ->with(self::equalTo($index))
            ->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexCommand($synchronizer);

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
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::never())->method('dropIndex');

        $command = new DeleteIndexCommand($synchronizer);

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
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('dropIndex')->with(self::equalTo($index));

        $command = new DeleteIndexCommand($synchronizer);

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
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('dropIndex')->with(self::equalTo($index));

        $command = new DeleteIndexCommand($synchronizer);

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
