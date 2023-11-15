<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Document;

use Exception;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\EmbeddedDocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\ModelDataProviderInterface;
use MeiliSearchBundle\DataProvider\PrimaryKeyOverrideDataProviderInterface;
use MeiliSearchBundle\DataProvider\PriorityDataProviderInterface;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\DocumentLoader;
use MeiliSearchBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentLoaderTest extends TestCase
{
    public function testLoaderCannotLoadDocumentOnEmptyProviders(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);

        $loader = new DocumentLoader($orchestrator, []);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('No providers found');
        $loader->load();
    }

    public function testLoaderCannotLoadDocumentOnException(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument')->willThrowException(
            new Exception('An error occurred')
        );

        $documentProvider = $this->createMock(DocumentDataProviderInterface::class);

        $loader = new DocumentLoader($orchestrator, [$documentProvider]);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        $loader->load();
    }

    public function testLoaderCannotLoadDocumentOnExceptionWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The document cannot be loaded, error: "An error occurred"')
        );

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument')->willThrowException(
            new Exception('An error occurred')
        );

        $documentProvider = $this->createMock(DocumentDataProviderInterface::class);

        $loader = new DocumentLoader($orchestrator, [$documentProvider], $logger);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        $loader->load();
    }

    public function testLoaderCanLoadWithoutOverridingPrimaryKey(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $documentProvider = $this->createMock(DocumentDataProviderInterface::class);
        $documentProvider->expects(self::once())->method('support');
        $documentProvider->expects(self::once())->method('getDocument');

        $loader = new DocumentLoader($orchestrator, [$documentProvider], $logger);
        $loader->load();
    }

    public function testLoaderCanLoadWithPrimaryKeyOverride(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $loader = new DocumentLoader($orchestrator, [new FooProvider()], $logger);
        $loader->load();
    }

    public function testLoaderCanLoadWithModelOverride(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $loader = new DocumentLoader($orchestrator, [new BarProvider()], $logger);
        $loader->load();
    }

    public function testLoaderCanFilterWithPriority(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::exactly(2))->method('addDocument');

        $loader = new DocumentLoader($orchestrator, [new BarProvider(), new PriorityProvider()], $logger);
        $loader->load();
    }

    public function testLoaderCanLoadEmbeddedDocuments(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument')->with(self::equalTo('foo'), self::equalTo([]));
        $orchestrator->expects(self::once())->method('addDocuments')->with(
            self::equalTo('foo'),
            self::equalTo([
                [
                    'id' => 1,
                    'title' => 'foo',
                ],
            ]),
            self::equalTo(null)
        );

        $loader = new DocumentLoader($orchestrator, [new EmbeddedProvider(), new PriorityProvider()], $logger);
        $loader->load();
    }

    public function testLoaderCanLoadDocumentsViaPriorityProvider(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::never())->method('addDocuments');
        $matcher = $this->exactly(2);
        $orchestrator->expects(self::exactly(2))->method('addDocument')
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->getInvocationCount()) {
                    0 => [self::equalTo('bar'), self::equalTo([]), self::equalTo(null), self::equalTo(null)],
                    default => [self::equalTo('foo'), self::equalTo([]), self::equalTo(null), self::equalTo(null)],
                };
            });

        $loader = new DocumentLoader($orchestrator, [new SecondPriorityProvider(), new PriorityProvider()], $logger);
        $loader->load();
    }
}

final class FooProvider implements DocumentDataProviderInterface, PrimaryKeyOverrideDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey(): string
    {
        return 'id';
    }
}

final class BarProvider implements
    DocumentDataProviderInterface,
    PrimaryKeyOverrideDataProviderInterface,
    ModelDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function getModel(): string
    {
        return stdClass::class;
    }
}

final class PriorityProvider implements DocumentDataProviderInterface, PriorityDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }
}

final class SecondPriorityProvider implements DocumentDataProviderInterface, PriorityDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'bar';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 1;
    }
}

final class EmbeddedProvider implements EmbeddedDocumentDataProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function support(): string
    {
        return 'foo';
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'foo',
            ],
        ];
    }
}
