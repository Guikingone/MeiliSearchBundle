<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Command\DeleteIndexesCommand;
use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new DeleteIndexesCommand($synchronizer);

        static::assertSame('meili:delete-indexes', $command->getName());
        static::assertSame('Delete every indexes stored in MeiliSearch', $command->getDescription());
    }

    public function testCommandCannotClearWithoutConfirmationAnswer(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::never())->method('dropIndexes');

        $command = new DeleteIndexesCommand($synchronizer);

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithoutConfirmation(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::never())->method('dropIndexes');

        $command = new DeleteIndexesCommand($synchronizer);

        $tester = new CommandTester($command);
        $tester->setInputs(['no']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('The action has been discarded', $tester->getDisplay());
    }

    public function testCommandCannotClearWithError(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('dropIndexes')->willThrowException(new Exception('An error occurred'));

        $command = new DeleteIndexesCommand($synchronizer);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('An error occurred when trying to removed all the indexes', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanClear(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('dropIndexes');

        $command = new DeleteIndexesCommand($synchronizer);

        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString('All the indexes have been removed', $tester->getDisplay());
    }
}
