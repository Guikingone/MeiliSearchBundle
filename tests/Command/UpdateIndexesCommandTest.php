<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearchBundle\Command\UpdateIndexesCommand;
use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new UpdateIndexesCommand([], $synchronizer);

        static::assertSame('meili:update-indexes', $command->getName());
        static::assertSame('Allow to update the indexes defined in the configuration', $command->getDescription());
        static::assertNotEmpty($command->getDefinition());
        static::assertTrue($command->getDefinition()->hasOption('force'));
        static::assertSame('f', $command->getDefinition()->getOption('force')->getShortcut());
        static::assertFalse($command->getDefinition()->getOption('force')->isValueRequired());
        static::assertSame(
            'Force the action without asking for confirmation',
            $command->getDefinition()->getOption('force')->getDescription()
        );
    }

    public function testCommandCannotUpdateEmptyIndexes(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new UpdateIndexesCommand([], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([
            '--force' => true,
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString(
            '[WARNING] No indexes found, please define at least a single index',
            $tester->getDisplay()
        );
    }

    public function testCommandCannotWarmIndexesWithException(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('updateIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ])
        )->willThrowException(new RuntimeException('Random error message'));

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([
            '--force' => true,
        ]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[ERROR] The indexes cannot be updated!', $tester->getDisplay());
        static::assertStringNotContainsString('Error: ": Random error message"', $tester->getDisplay());
    }

    public function testCommandCannotWarmIndexesWithPrefixButWithoutForceOptionOrConfirmation(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::never())->method('updateIndexes');

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer, '_foo_');
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[WARNING] The indexes update has been discarded', $tester->getDisplay());
    }

    public function testCommandCanWarmIndexesWithPrefixAndForceOption(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('updateIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ]),
            self::equalTo('_foo_')
        );

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer, '_foo_');
        $tester = new CommandTester($command);
        $tester->execute([
            '--force' => true,
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            '[OK] The indexes has been updated, feel free to query them!',
            $tester->getDisplay()
        );
    }

    public function testCommandCanWarmIndexesWithPrefix(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('updateIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ]),
            self::equalTo('_foo_')
        );

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer, '_foo_');
        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            '[OK] The indexes has been updated, feel free to query them!',
            $tester->getDisplay()
        );
    }

    public function testCommandCannotWarmIndexesButWithoutForceOptionOrConfirmation(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::never())->method('updateIndexes');

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[WARNING] The indexes update has been discarded', $tester->getDisplay());
    }

    public function testCommandCanWarmIndexesAndForceOption(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('updateIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ])
        );

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([
            '--force' => true,
        ]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            '[OK] The indexes has been updated, feel free to query them!',
            $tester->getDisplay()
        );
    }

    public function testCommandCanWarmIndexes(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('updateIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ])
        );

        $command = new UpdateIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->setInputs(['yes']);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            '[OK] The indexes has been updated, feel free to query them!',
            $tester->getDisplay()
        );
    }
}
