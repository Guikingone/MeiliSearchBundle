<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Command;

use Exception;
use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class WarmDocumentsCommandTest extends TestCase
{
    public function testCommandIsConfigured(): void
    {
        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);
        $dataProvider = $this->createMock(DocumentDataProviderInterface::class);

        $command = new WarmDocumentsCommand($documentOrchestrator, [$dataProvider]);

        static::assertSame('meili:warm-documents', $command->getName());
    }

    public function testCommandCannotWarmWithEmptyProviders(): void
    {
        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);

        $command = new WarmDocumentsCommand($documentOrchestrator, []);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => 'foo',
        ]);

        static::assertStringContainsString('No providers found, please be sure that you define at least a single provider', $tester->getDisplay());
        static::assertSame(0, $tester->getStatusCode());
    }

    public function testCommandCannotWarmWithProvidersAndAnException(): void
    {
        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);
        $documentOrchestrator->expects(self::once())->method('addDocument')->willThrowException(new Exception('An error occurred'));

        $dataProvider = $this->createMock(DocumentDataProviderInterface::class);
        $dataProvider->expects(self::once())->method('support')->willReturn('foo');
        $dataProvider->expects(self::once())->method('getDocument')->willReturn([
            [
                'id' => 'bar',
                'key' => 'foo',
            ],
        ]);
        $dataProvider->expects(self::once())->method('getPrimaryKey')->willReturn(null);

        $command = new WarmDocumentsCommand($documentOrchestrator, [$dataProvider]);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => 'foo',
        ]);

        static::assertStringContainsString('An error occurred when warming the documents, error: "An error occurred"', $tester->getDisplay());
        static::assertSame(1, $tester->getStatusCode());
    }

    public function testCommandCanWarmWithProviders(): void
    {
        $documentOrchestrator = $this->createMock(DocumentOrchestratorInterface::class);
        $documentOrchestrator->expects(self::once())->method('addDocument');

        $dataProvider = $this->createMock(DocumentDataProviderInterface::class);
        $dataProvider->expects(self::once())->method('support')->willReturn('foo');
        $dataProvider->expects(self::once())->method('getDocument')->willReturn([
            [
                'id' => 'bar',
                'key' => 'foo',
            ],
        ]);
        $dataProvider->expects(self::once())->method('getPrimaryKey')->willReturn(null);

        $command = new WarmDocumentsCommand($documentOrchestrator, [$dataProvider]);

        $tester = new CommandTester($command);
        $tester->execute([
            'index' => 'foo',
        ]);

        static::assertStringContainsString('The documents have been imported, feel free to search them!', $tester->getDisplay());
        static::assertSame(0, $tester->getStatusCode());
    }
}
