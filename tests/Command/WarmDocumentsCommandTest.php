<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Loader\LoaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmDocumentsCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $loader = $this->createMock(LoaderInterface::class);

        $command = new WarmDocumentsCommand($loader);
        static::assertSame('meili:warm-documents', $command->getName());
        static::assertSame('Warm the documents defined in DocumentDataProviders', $command->getDescription());
    }

    public function testCommandCannotWarmWithEmptyProviders(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::once())->method('load')->willThrowException(new RuntimeException('No providers found'));

        $command = new WarmDocumentsCommand($loader);
        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(1, $tester->getStatusCode());
        static::assertStringContainsString(
            '[ERROR] An error occurred during the documents warm process',
            $tester->getDisplay()
        );
        static::assertStringContainsString('Error: No providers found', $tester->getDisplay());
    }

    public function testCommandCanWarmWithProviders(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::once())->method('load');

        $command = new WarmDocumentsCommand($loader);

        $tester = new CommandTester($command);
        $tester->execute([]);

        static::assertSame(0, $tester->getStatusCode());
        static::assertStringContainsString(
            'The documents have been imported, feel free to search them!',
            $tester->getDisplay()
        );
    }
}
