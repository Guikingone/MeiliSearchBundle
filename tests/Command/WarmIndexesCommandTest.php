<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Command\WarmIndexesCommand;
use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmIndexesCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new WarmIndexesCommand([], $synchronizer);

        static::assertSame('meili:warm-indexes', $command->getName());
        static::assertSame('Allow to warm the indexes defined in the configuration', $command->getDescription());
    }

    public function testCommandCannotWarmEmptyIndexes(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);

        $command = new WarmIndexesCommand([], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString(
            '[WARNING] No indexes found, please define at least a single index',
            $tester->getDisplay()
        );
    }

    public function testCommandCannotWarmIndexesWithException(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('createIndexes')->willThrowException(
            new Exception('An error occurred')
        );

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString('[ERROR] The indexes cannot be warmed!', $tester->getDisplay());
        static::assertStringContainsString('Error: "An error occurred"', $tester->getDisplay());
    }

    public function testCommandCanWarmIndexesWithPrefix(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('createIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ]),
            self::equalTo('foo')
        );

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer, 'foo');
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            'The indexes has been warmed, feel free to query them!',
            $tester->getDisplay()
        );
    }

    public function testCommandCanWarmIndexes(): void
    {
        $synchronizer = $this->createMock(IndexSynchronizerInterface::class);
        $synchronizer->expects(self::once())->method('createIndexes')->with(
            self::equalTo([
                'foo' => [
                    'primaryKey' => 'id',
                    'synonyms' => [],
                ],
            ])
        );

        $command = new WarmIndexesCommand([
            'foo' => [
                'primaryKey' => 'id',
                'synonyms' => [],
            ],
        ], $synchronizer);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            'The indexes has been warmed, feel free to query them!',
            $tester->getDisplay()
        );
    }
}
