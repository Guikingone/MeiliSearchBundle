<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use Meilisearch\Client;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Command\ClearSearchResultCacheCommand;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Command\ListIndexesCommand;
use MeiliSearchBundle\Command\MigrateDocumentsCommand;
use MeiliSearchBundle\Command\UpdateIndexesCommand;
use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\Command\WarmIndexesCommand;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DependencyInjection\MeiliSearchExtension;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\DocumentLoader;
use MeiliSearchBundle\Document\DocumentMigrationOrchestrator;
use MeiliSearchBundle\Document\DocumentMigrationOrchestratorInterface;
use MeiliSearchBundle\Dump\DumpOrchestrator;
use MeiliSearchBundle\Dump\DumpOrchestratorInterface;
use MeiliSearchBundle\Event\Document\DocumentEventList;
use MeiliSearchBundle\Event\Document\DocumentEventListInterface;
use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\EventSubscriber\ClearDocumentOnNewSubscriber;
use MeiliSearchBundle\EventSubscriber\ClearDocumentOnUpdateSubscriber;
use MeiliSearchBundle\EventSubscriber\DocumentEventSubscriber;
use MeiliSearchBundle\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\EventSubscriber\IndexEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SearchEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SettingsEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SynonymsEventSubscriber;
use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use MeiliSearchBundle\Health\HealthEntryPoint;
use MeiliSearchBundle\Health\HealthEntryPointInterface;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\IndexSettingsOrchestrator;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\IndexSynchronizer;
use MeiliSearchBundle\Index\IndexSynchronizerInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Loader\LoaderInterface;
use MeiliSearchBundle\Messenger\Handler\AddIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\DeleteIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\AddDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\DeleteDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\UpdateDocumentMessageHandler;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use MeiliSearchBundle\Result\ResultBuilder;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use MeiliSearchBundle\Search\CachedSearchEntryPoint;
use MeiliSearchBundle\Search\FallbackSearchEntrypoint;
use MeiliSearchBundle\Search\SearchEntryPoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Settings\SettingsEntryPoint;
use MeiliSearchBundle\Settings\SettingsEntryPointInterface;
use MeiliSearchBundle\Twig\SearchExtension;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchExtensionTest extends TestCase
{
    public function testClientAndMetadataRegistryAndIndexSynchronizerCanBeConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([
            'meili_search' => [
                'host' => 'http://127.0.0.1:7700',
                'apiKey' => 'test',
            ],
        ], $container);

        static::assertTrue($container->hasDefinition(Client::class));
        static::assertNotEmpty($container->getDefinition(Client::class)->getArguments());
        static::assertSame('http://127.0.0.1:7700', $container->getDefinition(Client::class)->getArgument(0));
        static::assertSame('test', $container->getDefinition(Client::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(Client::class)->getArgument(2));
        static::assertFalse($container->getDefinition(Client::class)->isPublic());
        static::assertTrue($container->getDefinition(Client::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(Client::class)->getTag('container.preload')[0]);
        static::assertSame(
            Client::class,
            $container->getDefinition(Client::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(IndexMetadataRegistry::class));
        static::assertTrue($container->hasAlias(IndexMetadataRegistryInterface::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexMetadataRegistry::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexMetadataRegistry::class)->getArgument(1)
        );
        static::assertSame(
            '%kernel.project_dir%/var/_ms',
            $container->getDefinition(IndexMetadataRegistry::class)->getArgument(2)
        );
        static::assertFalse($container->getDefinition(IndexMetadataRegistry::class)->isPublic());
        static::assertTrue($container->getDefinition(IndexMetadataRegistry::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexMetadataRegistry::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexMetadataRegistry::class,
            $container->getDefinition(IndexMetadataRegistry::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(IndexSynchronizer::class));
        static::assertTrue($container->hasAlias(IndexSynchronizerInterface::class));
        static::assertNotEmpty($container->getDefinition(IndexSynchronizer::class)->getArguments());
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSynchronizer::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSynchronizer::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSynchronizer::class)->getArgument(2));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSynchronizer::class)->getArgument(3));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSynchronizer::class)->getArgument(4));
        static::assertFalse($container->getDefinition(IndexSynchronizer::class)->isPublic());
        static::assertTrue($container->getDefinition(IndexSynchronizer::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(IndexSynchronizer::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(IndexSynchronizer::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(IndexSynchronizer::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexSynchronizer::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexSynchronizer::class,
            $container->getDefinition(IndexSynchronizer::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testDefinitionsAreRegistered(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([
            'meili_search' => [
                'host' => 'http://127.0.0.1:7700',
                'apiKey' => 'test',
                'metadata_directory' => '%kernel.project_dir%/var/_ms',
            ],
        ], $container);

        static::assertTrue($container->hasDefinition(ResultBuilder::class));
        static::assertTrue($container->hasAlias(ResultBuilderInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ResultBuilder::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ResultBuilder::class)->getArgument(1));
        static::assertFalse($container->getDefinition(ResultBuilder::class)->isPublic());
        static::assertTrue($container->getDefinition(ResultBuilder::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(ResultBuilder::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ResultBuilder::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ResultBuilder::class,
            $container->getDefinition(ResultBuilder::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(IndexOrchestrator::class));
        static::assertTrue($container->hasAlias(IndexOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexOrchestrator::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexOrchestrator::class)->getArgument(2));
        static::assertFalse($container->getDefinition(IndexOrchestrator::class)->isPublic());
        static::assertTrue($container->getDefinition(IndexOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(IndexOrchestrator::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexOrchestrator::class,
            $container->getDefinition(IndexOrchestrator::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(IndexOrchestrator::class)->isPublic());

        static::assertTrue($container->hasDefinition(IndexSettingsOrchestrator::class));
        static::assertTrue($container->hasAlias(IndexSettingsOrchestratorInterface::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(2)
        );
        static::assertTrue($container->getDefinition(IndexSettingsOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(IndexSettingsOrchestrator::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexSettingsOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexSettingsOrchestrator::class,
            $container->getDefinition(IndexSettingsOrchestrator::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(IndexSettingsOrchestrator::class)->isPublic());

        static::assertTrue($container->hasDefinition(DocumentEntryPoint::class));
        static::assertTrue($container->hasAlias(DocumentEntryPointInterface::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEntryPoint::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEntryPoint::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEntryPoint::class)->getArgument(2)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEntryPoint::class)->getArgument(3)
        );
        static::assertTrue($container->getDefinition(DocumentEntryPoint::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(DocumentEntryPoint::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentEntryPoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentEntryPoint::class,
            $container->getDefinition(DocumentEntryPoint::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(DocumentEntryPoint::class)->isPublic());

        static::assertTrue($container->hasDefinition(DumpOrchestrator::class));
        static::assertTrue($container->hasAlias(DumpOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DumpOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DumpOrchestrator::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DumpOrchestrator::class)->getArgument(2));
        static::assertTrue($container->getDefinition(DumpOrchestrator::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(DumpOrchestrator::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(DumpOrchestrator::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(DumpOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(DumpOrchestrator::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DumpOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DumpOrchestrator::class,
            $container->getDefinition(DumpOrchestrator::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(DumpOrchestrator::class)->isPublic());

        static::assertTrue($container->hasDefinition(SynonymsOrchestrator::class));
        static::assertTrue($container->hasAlias(SynonymsOrchestratorInterface::class));
        static::assertNotEmpty($container->getDefinition(SynonymsOrchestrator::class)->getArguments());
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SynonymsOrchestrator::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SynonymsOrchestrator::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SynonymsOrchestrator::class)->getArgument(2)
        );
        static::assertTrue($container->getDefinition(SynonymsOrchestrator::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(SynonymsOrchestrator::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(SynonymsOrchestrator::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(SynonymsOrchestrator::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SynonymsOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SynonymsOrchestrator::class,
            $container->getDefinition(SynonymsOrchestrator::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(SynonymsOrchestrator::class)->isPublic());

        static::assertTrue($container->hasDefinition(UpdateOrchestrator::class));
        static::assertTrue($container->hasAlias(UpdateOrchestratorInterface::class));
        static::assertNotEmpty($container->getDefinition(UpdateOrchestrator::class)->getArguments());
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(UpdateOrchestrator::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(UpdateOrchestrator::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(UpdateOrchestrator::class)->isPublic());
        static::assertTrue($container->getDefinition(UpdateOrchestrator::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(UpdateOrchestrator::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(UpdateOrchestrator::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(UpdateOrchestrator::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(UpdateOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            UpdateOrchestrator::class,
            $container->getDefinition(UpdateOrchestrator::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(SettingsEntryPoint::class));
        static::assertTrue($container->hasAlias(SettingsEntryPointInterface::class));
        static::assertNotEmpty($container->getDefinition(SettingsEntryPoint::class)->getArguments());
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SettingsEntryPoint::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SettingsEntryPoint::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(SettingsEntryPoint::class)->isPublic());
        static::assertTrue($container->getDefinition(SettingsEntryPoint::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(SettingsEntryPoint::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(SettingsEntryPoint::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(SettingsEntryPoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SettingsEntryPoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SettingsEntryPoint::class,
            $container->getDefinition(SettingsEntryPoint::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(DocumentMigrationOrchestrator::class));
        static::assertTrue($container->hasAlias(DocumentMigrationOrchestratorInterface::class));
        static::assertNotEmpty($container->getDefinition(DocumentMigrationOrchestrator::class)->getArguments());
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getArgument(2)
        );
        static::assertFalse($container->getDefinition(DocumentMigrationOrchestrator::class)->isPublic());
        static::assertTrue($container->getDefinition(DocumentMigrationOrchestrator::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue(
            $container->getDefinition(DocumentMigrationOrchestrator::class)->hasTag('container.preload')
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentMigrationOrchestrator::class,
            $container->getDefinition(DocumentMigrationOrchestrator::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(DocumentLoader::class));
        static::assertNotEmpty($container->getDefinition(DocumentLoader::class)->getArguments());
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentLoader::class)->getArgument(0));
        static::assertInstanceOf(
            TaggedIteratorArgument::class,
            $container->getDefinition(DocumentLoader::class)->getArgument(1)
        );
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentLoader::class)->getArgument(2));
        static::assertTrue($container->getDefinition(DocumentLoader::class)->hasTag('meili_search.document_loader'));
        static::assertTrue($container->getDefinition(DocumentLoader::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(DocumentLoader::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(DocumentLoader::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(DocumentLoader::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentLoader::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentLoader::class,
            $container->getDefinition(DocumentLoader::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(DocumentLoader::class)->isPublic());

        static::assertTrue($container->hasDefinition(SearchEntryPoint::class));
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));

        static::assertTrue($container->hasDefinition(DocumentNormalizer::class));
        static::assertTrue($container->hasDefinition(IndexMetadataRegistry::class));
        static::assertTrue($container->hasDefinition(DocumentEventSubscriber::class));
        static::assertTrue($container->hasDefinition(ExceptionSubscriber::class));
        static::assertTrue($container->hasDefinition(IndexEventSubscriber::class));
        static::assertTrue($container->hasDefinition(SearchEventSubscriber::class));
        static::assertTrue($container->hasDefinition(SynonymsEventSubscriber::class));

        static::assertArrayHasKey(LoaderInterface::class, $container->getAutoconfiguredInstanceof());
        static::assertArrayHasKey(DocumentDataProviderInterface::class, $container->getAutoconfiguredInstanceof());
        static::assertArrayHasKey(TraceableDataCollectorInterface::class, $container->getAutoconfiguredInstanceof());

        static::assertFalse($container->has(SearchResultCacheOrchestrator::class));
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
    }

    public function testFormCanBeConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->register(FormFactory::class, FormFactory::class);
        $container->setAlias(FormFactoryInterface::class, FormFactory::class);
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(MeiliSearchChoiceType::class));
        static::assertTrue($container->getDefinition(MeiliSearchChoiceType::class)->hasTag('form.type'));
        static::assertTrue($container->getDefinition(MeiliSearchChoiceType::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(MeiliSearchChoiceType::class)->getArguments());
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(MeiliSearchChoiceType::class)->getArgument(0)
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(MeiliSearchChoiceType::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            MeiliSearchChoiceType::class,
            $container->getDefinition(MeiliSearchChoiceType::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(MeiliSearchChoiceType::class)->isPublic());
    }

    public function testDoctrineDocumentSubscriberCanBeConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(DocumentReader::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentReader::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DocumentReader::class)->hasTag('meili_search.annotation_reader'));
        static::assertTrue($container->getDefinition(DocumentReader::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentReader::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentReader::class,
            $container->getDefinition(DocumentReader::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(DocumentReader::class)->isPublic());

        static::assertTrue($container->hasDefinition(DocumentSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(2)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(3)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(4)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentSubscriber::class)->getArgument(5)
        );
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('doctrine.event_subscriber'));
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('meili_search.subscriber'));
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(DocumentSubscriber::class)->getArguments());
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentSubscriber::class,
            $container->getDefinition(DocumentSubscriber::class)->getTag('container.preload')[0]['class']
        );
        static::assertFalse($container->getDefinition(DocumentSubscriber::class)->isPublic());
    }

    public function testCommandsAreConfiguredWithCache(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([
            'meili_search' => [
                'cache' => [
                    'enabled' => true,
                    'pool' => 'app',
                    'clear_on_new_document' => true,
                    'clear_on_document_update' => true,
                    'fallback' => true,
                ],
                'indexes' => [
                    'foo' => [
                        'primaryKey' => 'id',
                    ],
                ],
            ],
        ], $container);

        static::assertTrue($container->has(SearchResultCacheOrchestrator::class));
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
        static::assertTrue($container->hasDefinition(SearchResultCacheOrchestrator::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(SearchResultCacheOrchestrator::class)->isPublic());
        static::assertTrue($container->getDefinition(SearchResultCacheOrchestrator::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue(
            $container->getDefinition(SearchResultCacheOrchestrator::class)->hasTag('container.preload')
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SearchResultCacheOrchestrator::class,
            $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(CachedSearchEntryPoint::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(CachedSearchEntryPoint::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(CachedSearchEntryPoint::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(CachedSearchEntryPoint::class)->isPublic());
        static::assertTrue($container->getDefinition(CachedSearchEntryPoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(CachedSearchEntryPoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            CachedSearchEntryPoint::class,
            $container->getDefinition(CachedSearchEntryPoint::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
        static::assertArrayHasKey(SearchEntryPointInterface::class, $container->getAutoconfiguredInstanceof());

        static::assertTrue($container->hasDefinition(ClearSearchResultCacheCommand::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(ClearSearchResultCacheCommand::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(ClearSearchResultCacheCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(ClearSearchResultCacheCommand::class)->hasTag('console.command'));
        static::assertTrue(
            $container->getDefinition(ClearSearchResultCacheCommand::class)->hasTag('container.preload')
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ClearSearchResultCacheCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ClearSearchResultCacheCommand::class,
            $container->getDefinition(ClearSearchResultCacheCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->has(ClearDocumentOnNewSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(ClearDocumentOnNewSubscriber::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(ClearDocumentOnNewSubscriber::class)->isPublic());
        static::assertTrue($container->getDefinition(ClearDocumentOnNewSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ClearDocumentOnNewSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ClearDocumentOnNewSubscriber::class,
            $container->getDefinition(ClearDocumentOnNewSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->has(ClearDocumentOnUpdateSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(ClearDocumentOnUpdateSubscriber::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(ClearDocumentOnUpdateSubscriber::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(ClearDocumentOnUpdateSubscriber::class)->hasTag('container.preload')
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ClearDocumentOnUpdateSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ClearDocumentOnUpdateSubscriber::class,
            $container->getDefinition(ClearDocumentOnUpdateSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(DeleteIndexCommand::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DeleteIndexCommand::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(DeleteIndexCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(DeleteIndexCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(DeleteIndexCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DeleteIndexCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DeleteIndexCommand::class,
            $container->getDefinition(DeleteIndexCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(ListIndexesCommand::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(ListIndexesCommand::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(ListIndexesCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(ListIndexesCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(ListIndexesCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ListIndexesCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ListIndexesCommand::class,
            $container->getDefinition(ListIndexesCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(WarmDocumentsCommand::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(WarmDocumentsCommand::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(WarmDocumentsCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(WarmDocumentsCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(WarmDocumentsCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(WarmDocumentsCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            WarmDocumentsCommand::class,
            $container->getDefinition(WarmDocumentsCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(WarmIndexesCommand::class));
        static::assertNotEmpty($container->getDefinition(WarmIndexesCommand::class)->getArgument(0));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(WarmIndexesCommand::class)->getArgument(1)
        );
        static::assertNull($container->getDefinition(WarmIndexesCommand::class)->getArgument(2));
        static::assertFalse($container->getDefinition(WarmIndexesCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(WarmIndexesCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(WarmIndexesCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(WarmIndexesCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            WarmIndexesCommand::class,
            $container->getDefinition(WarmIndexesCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(UpdateIndexesCommand::class));
        static::assertNotEmpty($container->getDefinition(UpdateIndexesCommand::class)->getArgument(0));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(UpdateIndexesCommand::class)->getArgument(1)
        );
        static::assertNull($container->getDefinition(UpdateIndexesCommand::class)->getArgument(2));
        static::assertFalse($container->getDefinition(UpdateIndexesCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(UpdateIndexesCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(UpdateIndexesCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(UpdateIndexesCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            UpdateIndexesCommand::class,
            $container->getDefinition(UpdateIndexesCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(MigrateDocumentsCommand::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(MigrateDocumentsCommand::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(MigrateDocumentsCommand::class)->isPublic());
        static::assertTrue($container->getDefinition(MigrateDocumentsCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(MigrateDocumentsCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(MigrateDocumentsCommand::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            MigrateDocumentsCommand::class,
            $container->getDefinition(MigrateDocumentsCommand::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(FallbackSearchEntrypoint::class));
        static::assertIsArray($container->getDefinition(FallbackSearchEntrypoint::class)->getArgument(0));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(FallbackSearchEntrypoint::class)->getArgument(0)[0]
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(FallbackSearchEntrypoint::class)->getArgument(0)[1]
        );
        static::assertFalse($container->getDefinition(FallbackSearchEntrypoint::class)->isPublic());
        static::assertTrue($container->getDefinition(FallbackSearchEntrypoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(FallbackSearchEntrypoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            FallbackSearchEntrypoint::class,
            $container->getDefinition(FallbackSearchEntrypoint::class)->getTag('container.preload')[0]['class']
        );
        static::assertSame(
            FallbackSearchEntrypoint::class,
            (string)$container->getAlias(SearchEntryPointInterface::class)
        );
    }

    public function testCommandsAreConfiguredWithoutCache(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([], $container);

        static::assertFalse($container->hasDefinition(SearchResultCacheOrchestrator::class));
        static::assertFalse($container->hasDefinition(ClearSearchResultCacheCommand::class));
        static::assertTrue($container->hasDefinition(DeleteIndexCommand::class));
        static::assertTrue($container->hasDefinition(ListIndexesCommand::class));
        static::assertTrue($container->hasDefinition(WarmDocumentsCommand::class));
    }

    public function testSerializerIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(DocumentNormalizer::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentNormalizer::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentNormalizer::class)->getArgument(1)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentNormalizer::class)->getArgument(2)
        );
        static::assertFalse($container->getDefinition(DocumentNormalizer::class)->isPublic());
        static::assertTrue($container->getDefinition(DocumentNormalizer::class)->hasTag('serializer.normalizer'));
        static::assertTrue($container->getDefinition(DocumentNormalizer::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentNormalizer::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentNormalizer::class,
            $container->getDefinition(DocumentNormalizer::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(UuidNormalizer::class));
        static::assertFalse($container->getDefinition(UuidNormalizer::class)->isPublic());
        static::assertTrue($container->getDefinition(UuidNormalizer::class)->hasTag('serializer.normalizer'));
        static::assertTrue($container->getDefinition(UuidNormalizer::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(UuidNormalizer::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            UuidNormalizer::class,
            $container->getDefinition(UuidNormalizer::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(UuidDenormalizer::class));
        static::assertFalse($container->getDefinition(UuidDenormalizer::class)->isPublic());
        static::assertTrue($container->getDefinition(UuidDenormalizer::class)->hasTag('serializer.normalizer'));
        static::assertTrue($container->getDefinition(UuidDenormalizer::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(UuidDenormalizer::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            UuidDenormalizer::class,
            $container->getDefinition(UuidDenormalizer::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testMessengerHandlersAreConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(AddIndexMessageHandler::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(AddIndexMessageHandler::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(AddIndexMessageHandler::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(AddIndexMessageHandler::class)->hasTag('messenger.message_handler')
        );
        static::assertTrue($container->getDefinition(AddIndexMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(AddIndexMessageHandler::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            AddIndexMessageHandler::class,
            $container->getDefinition(AddIndexMessageHandler::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(DeleteIndexMessageHandler::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DeleteIndexMessageHandler::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(DeleteIndexMessageHandler::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(DeleteIndexMessageHandler::class)->hasTag('messenger.message_handler')
        );
        static::assertTrue($container->getDefinition(DeleteIndexMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DeleteIndexMessageHandler::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DeleteIndexMessageHandler::class,
            $container->getDefinition(DeleteIndexMessageHandler::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(AddDocumentMessageHandler::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(AddDocumentMessageHandler::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(AddDocumentMessageHandler::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(AddDocumentMessageHandler::class)->hasTag('messenger.message_handler')
        );
        static::assertTrue($container->getDefinition(AddDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(AddDocumentMessageHandler::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            AddDocumentMessageHandler::class,
            $container->getDefinition(AddDocumentMessageHandler::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(DeleteDocumentMessageHandler::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DeleteDocumentMessageHandler::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(DeleteDocumentMessageHandler::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(DeleteDocumentMessageHandler::class)->hasTag('messenger.message_handler')
        );
        static::assertTrue($container->getDefinition(DeleteDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DeleteDocumentMessageHandler::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DeleteDocumentMessageHandler::class,
            $container->getDefinition(DeleteDocumentMessageHandler::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(UpdateDocumentMessageHandler::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(UpdateDocumentMessageHandler::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(UpdateDocumentMessageHandler::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(UpdateDocumentMessageHandler::class)->hasTag('messenger.message_handler')
        );
        static::assertTrue($container->getDefinition(UpdateDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(UpdateDocumentMessageHandler::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            UpdateDocumentMessageHandler::class,
            $container->getDefinition(UpdateDocumentMessageHandler::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testTwigIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(SearchExtension::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchExtension::class)->getArgument(0));
        static::assertFalse($container->getDefinition(SearchExtension::class)->isPublic());
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('twig.extension'));
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('twig.runtime'));
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SearchExtension::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SearchExtension::class,
            $container->getDefinition(SearchExtension::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testEventListAreConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasAlias(DocumentEventListInterface::class));
        static::assertTrue($container->hasDefinition(DocumentEventList::class));
        static::assertFalse($container->getDefinition(DocumentEventList::class)->isPublic());
        static::assertTrue($container->getDefinition(DocumentEventList::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentEventList::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentEventList::class,
            $container->getDefinition(DocumentEventList::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasAlias(IndexEventListInterface::class));
        static::assertTrue($container->hasDefinition(IndexEventList::class));
        static::assertFalse($container->getDefinition(IndexEventList::class)->isPublic());
        static::assertTrue($container->getDefinition(IndexEventList::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexEventList::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexEventList::class,
            $container->getDefinition(IndexEventList::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasAlias(SearchEventListInterface::class));
        static::assertTrue($container->hasDefinition(SearchEventList::class));
        static::assertFalse($container->getDefinition(SearchEventList::class)->isPublic());
        static::assertTrue($container->getDefinition(SearchEventList::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SearchEventList::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SearchEventList::class,
            $container->getDefinition(SearchEventList::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testSubscribersAreConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(DocumentEventSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEventSubscriber::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(DocumentEventSubscriber::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(DocumentEventSubscriber::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(DocumentEventSubscriber::class)->hasTag('kernel.event_subscriber')
        );
        static::assertTrue($container->getDefinition(DocumentEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(DocumentEventSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            DocumentEventSubscriber::class,
            $container->getDefinition(DocumentEventSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(ExceptionSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(ExceptionSubscriber::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(ExceptionSubscriber::class)->isPublic());
        static::assertTrue($container->getDefinition(ExceptionSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(ExceptionSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(ExceptionSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            ExceptionSubscriber::class,
            $container->getDefinition(ExceptionSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(IndexEventSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexEventSubscriber::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(IndexEventSubscriber::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(IndexEventSubscriber::class)->isPublic());
        static::assertTrue($container->getDefinition(IndexEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(IndexEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(IndexEventSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            IndexEventSubscriber::class,
            $container->getDefinition(IndexEventSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(SearchEventSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SearchEventSubscriber::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SearchEventSubscriber::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(SearchEventSubscriber::class)->isPublic());
        static::assertTrue($container->getDefinition(SearchEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(SearchEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SearchEventSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SearchEventSubscriber::class,
            $container->getDefinition(SearchEventSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(SettingsEventSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SettingsEventSubscriber::class)->getArgument(0)
        );
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SettingsEventSubscriber::class)->getArgument(1)
        );
        static::assertFalse($container->getDefinition(SettingsEventSubscriber::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(SettingsEventSubscriber::class)->hasTag('kernel.event_subscriber')
        );
        static::assertTrue($container->getDefinition(SettingsEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SettingsEventSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SettingsEventSubscriber::class,
            $container->getDefinition(SettingsEventSubscriber::class)->getTag('container.preload')[0]['class']
        );

        static::assertTrue($container->hasDefinition(SynonymsEventSubscriber::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(SynonymsEventSubscriber::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(SynonymsEventSubscriber::class)->isPublic());
        static::assertTrue(
            $container->getDefinition(SynonymsEventSubscriber::class)->hasTag('kernel.event_subscriber')
        );
        static::assertTrue($container->getDefinition(SynonymsEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SynonymsEventSubscriber::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SynonymsEventSubscriber::class,
            $container->getDefinition(SynonymsEventSubscriber::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testSearchEntryPointIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(SearchEntryPoint::class));
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(2));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(3));
        static::assertNull($container->getDefinition(SearchEntryPoint::class)->getArgument(4));
        static::assertFalse($container->getDefinition(SearchEntryPoint::class)->isPublic());
        static::assertTrue($container->getDefinition(SearchEntryPoint::class)->hasTag('monolog.logger'));
        static::assertArrayHasKey(
            'channel',
            $container->getDefinition(SearchEntryPoint::class)->getTag('monolog.logger')[0]
        );
        static::assertSame(
            'meilisearch',
            $container->getDefinition(SearchEntryPoint::class)->getTag('monolog.logger')[0]['channel']
        );
        static::assertTrue($container->getDefinition(SearchEntryPoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(SearchEntryPoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            SearchEntryPoint::class,
            $container->getDefinition(SearchEntryPoint::class)->getTag('container.preload')[0]['class']
        );
        static::assertArrayHasKey(SearchEntryPointInterface::class, $container->getAutoconfiguredInstanceof());
    }

    public function testHealthEntrypointIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(HealthEntryPoint::class));
        static::assertTrue($container->hasAlias(HealthEntryPointInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(HealthEntryPoint::class)->getArgument(0));
        static::assertFalse($container->getDefinition(HealthEntryPoint::class)->isPublic());
        static::assertTrue($container->getDefinition(HealthEntryPoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(HealthEntryPoint::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            HealthEntryPoint::class,
            $container->getDefinition(HealthEntryPoint::class)->getTag('container.preload')[0]['class']
        );
    }

    public function testDataCollectorIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(MeiliSearchBundleDataCollector::class));
        static::assertInstanceOf(
            Reference::class,
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->getArgument(0)
        );
        static::assertFalse($container->getDefinition(MeiliSearchBundleDataCollector::class)->isPublic());
        static::assertTrue($container->getDefinition(MeiliSearchBundleDataCollector::class)->hasTag('data_collector'));
        static::assertSame(
            '@MeiliSearch/Collector/data_collector.html.twig',
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('data_collector')[0]['template']
        );
        static::assertSame(
            MeiliSearchBundleDataCollector::NAME,
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('data_collector')[0]['id']
        );
        static::assertTrue(
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->hasTag('container.preload')
        );
        static::assertArrayHasKey(
            'class',
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('container.preload')[0]
        );
        static::assertSame(
            MeiliSearchBundleDataCollector::class,
            $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('container.preload')[0]['class']
        );
    }
}
