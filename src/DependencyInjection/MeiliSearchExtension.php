<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use Meilisearch\Client;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
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
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;
use function interface_exists;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchExtension extends Extension
{
    private const ANNOTATION_READER_TAG = 'meili_search.annotation_reader';

    private const DOCUMENT_PROVIDER_IDENTIFIER = 'meili_search.document_provider';

    private const LOADER_TAG = 'meili_search.loader';

    private const LOADER_LIST = [
        'document' => 'meili_search.document_loader',
        'index' => 'meili_search.index_loader',
    ];

    private const CACHE = 'cache';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->registerClientAndMetadataRegistry($container, $config);
        $this->handleCacheConfiguration($container, $config);
        $this->registerResultBuilder($container);
        $this->registerOrchestrator($container);
        $this->registerLoaders($container);
        $this->registerForm($container);
        $this->registerDoctrineSubscribers($container);
        $this->registerSerializer($container);
        $this->registerMessengerHandler($container);
        $this->registerTwig($container);
        $this->registerSearchEntryPoint($container, $config);
        $this->registerEventList($container);
        $this->registerSubscribers($container);
        $this->registerCommands($container, $config);
        $this->registerHealthEntryPoint($container);
        $this->registerDataCollector($container);

        $container->registerForAutoconfiguration(DocumentDataProviderInterface::class)
            ->addTag(self::DOCUMENT_PROVIDER_IDENTIFIER);
        $container->registerForAutoconfiguration(TraceableDataCollectorInterface::class)
            ->addTag('meili_search.data_collector.traceable');
        $container->registerForAutoconfiguration(SearchEntryPointInterface::class)
            ->addTag('meili_search.search_entry_point');
    }

    private function registerClientAndMetadataRegistry(ContainerBuilder $container, array $configuration): void
    {
        $container->register(Client::class, Client::class)
            ->setArguments([
                $configuration['host'],
                $configuration['apiKey'] ?? null,
                new Reference('http_client', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => Client::class,
            ]);

        $container->register(IndexMetadataRegistry::class, IndexMetadataRegistry::class)
            ->setArguments([
                new Reference('filesystem', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference('serializer', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                $configuration['metadata_directory'],
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexMetadataRegistry::class,
            ]);

        $container->setAlias(IndexMetadataRegistryInterface::class, IndexMetadataRegistry::class);

        $container->register(IndexSynchronizer::class, IndexSynchronizer::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(
                    IndexMetadataRegistryInterface::class,
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                ),
                new Reference(HealthEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(SettingsEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => IndexSynchronizer::class,
            ]);

        $container->setAlias(IndexSynchronizerInterface::class, IndexSynchronizer::class);
    }

    private function handleCacheConfiguration(ContainerBuilder $container, array $configuration): void
    {
        if (!array_key_exists(self::CACHE, $configuration) || !$configuration[self::CACHE]['enabled']) {
            return;
        }

        if ($configuration[self::CACHE]['clear_on_new_document']) {
            $container->register(ClearDocumentOnNewSubscriber::class, ClearDocumentOnNewSubscriber::class)
                ->setArguments([
                    new Reference(
                        SearchResultCacheOrchestratorInterface::class,
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                    ),
                ])
                ->setPublic(false)
                ->addTag('kernel.event_subscriber')
                ->addTag('container.preload', [
                    'class' => ClearDocumentOnNewSubscriber::class,
                ]);
        }

        if ($configuration[self::CACHE]['clear_on_document_update']) {
            $container->register(ClearDocumentOnUpdateSubscriber::class, ClearDocumentOnUpdateSubscriber::class)
                ->setArguments([
                    new Reference(
                        SearchResultCacheOrchestratorInterface::class,
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                    ),
                ])
                ->setPublic(false)
                ->addTag('kernel.event_subscriber')
                ->addTag('container.preload', [
                    'class' => ClearDocumentOnUpdateSubscriber::class,
                ]);
        }
    }

    private function registerResultBuilder(ContainerBuilder $container): void
    {
        $container->register(ResultBuilder::class, ResultBuilder::class)
            ->setArguments([
                new Reference(SerializerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => ResultBuilder::class,
            ]);
        $container->setAlias(ResultBuilderInterface::class, ResultBuilder::class);
    }

    private function registerOrchestrator(ContainerBuilder $container): void
    {
        $container->register(IndexOrchestrator::class, IndexOrchestrator::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexOrchestrator::class,
            ]);
        $container->setAlias(IndexOrchestratorInterface::class, IndexOrchestrator::class);

        $container->register(IndexSettingsOrchestrator::class, IndexSettingsOrchestrator::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexSettingsOrchestrator::class,
            ]);
        $container->setAlias(IndexSettingsOrchestratorInterface::class, IndexSettingsOrchestrator::class);

        $container->register(DocumentEntryPoint::class, DocumentEntryPoint::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(ResultBuilderInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => DocumentEntryPoint::class,
            ]);
        $container->setAlias(DocumentEntryPointInterface::class, DocumentEntryPoint::class);

        $container->register(DumpOrchestrator::class, DumpOrchestrator::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => DumpOrchestrator::class,
            ]);
        $container->setAlias(DumpOrchestratorInterface::class, DumpOrchestrator::class);

        $container->register(SynonymsOrchestrator::class, SynonymsOrchestrator::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => SynonymsOrchestrator::class,
            ]);
        $container->setAlias(SynonymsOrchestratorInterface::class, SynonymsOrchestrator::class);

        $container->register(UpdateOrchestrator::class, UpdateOrchestrator::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => UpdateOrchestrator::class,
            ]);

        $container->setAlias(UpdateOrchestratorInterface::class, UpdateOrchestrator::class);

        $container->register(SettingsEntryPoint::class, SettingsEntryPoint::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => SettingsEntryPoint::class,
            ]);

        $container->setAlias(SettingsEntryPointInterface::class, SettingsEntryPoint::class);

        $container->register(DocumentMigrationOrchestrator::class, DocumentMigrationOrchestrator::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(DumpOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => DocumentMigrationOrchestrator::class,
            ]);

        $container->setAlias(DocumentMigrationOrchestratorInterface::class, DocumentMigrationOrchestrator::class);
    }

    private function registerLoaders(ContainerBuilder $container): void
    {
        $container->register(DocumentLoader::class, DocumentLoader::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new TaggedIteratorArgument(self::DOCUMENT_PROVIDER_IDENTIFIER),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag(self::LOADER_LIST['document'])
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => DocumentLoader::class,
            ]);

        $container->registerForAutoconfiguration(LoaderInterface::class)->addTag(self::LOADER_TAG);
    }

    private function registerForm(ContainerBuilder $container): void
    {
        $container->register(MeiliSearchChoiceType::class, MeiliSearchChoiceType::class)
            ->setArguments([
                new Reference(SearchEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('form.type')
            ->addTag('container.preload', [
                'class' => MeiliSearchChoiceType::class,
            ]);
    }

    private function registerDoctrineSubscribers(ContainerBuilder $container): void
    {
        $container->register(DocumentReader::class, DocumentReader::class)
            ->setArguments([
                new Reference('annotation_reader', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag(self::ANNOTATION_READER_TAG)
            ->addTag('container.preload', [
                'class' => DocumentReader::class,
            ]);

        $container->register(DocumentSubscriber::class, DocumentSubscriber::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(DocumentReader::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(
                    IndexMetadataRegistryInterface::class,
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                ),
                new Reference('property_accessor', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(SerializerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(MessageBusInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('meili_search.subscriber')
            ->addTag('doctrine.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentSubscriber::class,
            ]);
    }

    private function registerSerializer(ContainerBuilder $container): void
    {
        if (!$container->has(DocumentReader::class)) {
            return;
        }

        $container->register(DocumentNormalizer::class, DocumentNormalizer::class)
            ->setArguments([
                new Reference(DocumentReader::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(ObjectNormalizer::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(PropertyAccessorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('serializer.normalizer')
            ->addTag('container.preload', [
                'class' => DocumentNormalizer::class,
            ]);

        if (interface_exists(UuidInterface::class)) {
            $container->register(UuidNormalizer::class, UuidNormalizer::class)
                ->setPublic(false)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidNormalizer::class,
                ]);

            $container->register(UuidDenormalizer::class, UuidDenormalizer::class)
                ->setPublic(false)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidDenormalizer::class,
                ]);
        }
    }

    private function registerSearchEntryPoint(ContainerBuilder $container, array $configuration): void
    {
        $container->register(SearchEntryPoint::class, SearchEntryPoint::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(ResultBuilderInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                $configuration['prefix'],
            ])
            ->setPublic(false)
            ->addTag('monolog.logger', [
                'channel' => 'meilisearch',
            ])
            ->addTag('container.preload', [
                'class' => SearchEntryPoint::class,
            ]);

        $container->setAlias(SearchEntryPointInterface::class, SearchEntryPoint::class);

        if (array_key_exists(self::CACHE, $configuration) && $configuration[self::CACHE]['enabled']) {
            $container->register(SearchResultCacheOrchestrator::class, SearchResultCacheOrchestrator::class)
                ->setArguments([
                    new Reference(CacheItemPoolInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                    new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ])
                ->setPublic(false)
                ->addTag('monolog.logger', [
                    'channel' => 'meilisearch',
                ])
                ->addTag('container.preload', [
                    'class' => SearchResultCacheOrchestrator::class,
                ]);

            $container->register(CachedSearchEntryPoint::class, CachedSearchEntryPoint::class)
                ->setArguments([
                    new Reference(
                        SearchResultCacheOrchestrator::class,
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                    ),
                    new Reference(SearchEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                ])
                ->setPublic(false)
                ->addTag('container.preload', [
                    'class' => CachedSearchEntryPoint::class,
                ]);

            $container->setAlias(SearchEntryPointInterface::class, CachedSearchEntryPoint::class);

            if ($configuration[self::CACHE]['fallback']) {
                $container->register(FallbackSearchEntrypoint::class, FallbackSearchEntrypoint::class)
                    ->setArguments([
                        [
                            new Reference(SearchEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                            new Reference(
                                CachedSearchEntryPoint::class,
                                ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                            ),
                        ],
                    ])
                    ->setPublic(false)
                    ->addTag('container.preload', [
                        'class' => FallbackSearchEntrypoint::class,
                    ]);

                $container->setAlias(SearchEntryPointInterface::class, FallbackSearchEntrypoint::class);
            }
        }
    }

    private function registerMessengerHandler(ContainerBuilder $container): void
    {
        if (!$container->has(MessageBusInterface::class)) {
            return;
        }

        $container->register(AddIndexMessageHandler::class, AddIndexMessageHandler::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddIndexMessageHandler::class,
            ]);

        $container->register(DeleteIndexMessageHandler::class, DeleteIndexMessageHandler::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteIndexMessageHandler::class,
            ]);

        $container->register(AddDocumentMessageHandler::class, AddDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddDocumentMessageHandler::class,
            ]);

        $container->register(DeleteDocumentMessageHandler::class, DeleteDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteDocumentMessageHandler::class,
            ]);

        $container->register(UpdateDocumentMessageHandler::class, UpdateDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => UpdateDocumentMessageHandler::class,
            ]);
    }

    private function registerTwig(ContainerBuilder $container): void
    {
        $container->register(SearchExtension::class, SearchExtension::class)
            ->setArguments([
                new Reference(SearchEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('twig.extension')
            ->addTag('twig.runtime')
            ->addTag('container.preload', [
                'class' => SearchExtension::class,
            ]);
    }

    private function registerEventList(ContainerBuilder $container): void
    {
        $container->register(DocumentEventList::class, DocumentEventList::class)
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => DocumentEventList::class,
            ]);

        $container->setAlias(DocumentEventListInterface::class, DocumentEventList::class);

        $container->register(IndexEventList::class, IndexEventList::class)
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexEventList::class,
            ]);

        $container->setAlias(IndexEventListInterface::class, IndexEventList::class);

        $container->register(SearchEventList::class, SearchEventList::class)
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => SearchEventList::class,
            ]);

        $container->setAlias(SearchEventListInterface::class, SearchEventList::class);
    }

    private function registerSubscribers(ContainerBuilder $container): void
    {
        $container->register(DocumentEventSubscriber::class, DocumentEventSubscriber::class)
            ->setArguments([
                new Reference(DocumentEventListInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentEventSubscriber::class,
            ]);

        $container->register(ExceptionSubscriber::class, ExceptionSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => ExceptionSubscriber::class,
            ]);

        $container->register(IndexEventSubscriber::class, IndexEventSubscriber::class)
            ->setArguments([
                new Reference(IndexEventListInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => IndexEventSubscriber::class,
            ]);

        $container->register(SearchEventSubscriber::class, SearchEventSubscriber::class)
            ->setArguments([
                new Reference(SearchEventListInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SearchEventSubscriber::class,
            ]);

        $container->register(SettingsEventSubscriber::class, SettingsEventSubscriber::class)
            ->setArguments([
                new Reference(IndexEventListInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SettingsEventSubscriber::class,
            ]);

        $container->register(SynonymsEventSubscriber::class, SynonymsEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SynonymsEventSubscriber::class,
            ]);
    }

    private function registerCommands(ContainerBuilder $container, array $configuration): void
    {
        if (array_key_exists(self::CACHE, $configuration) && $configuration[self::CACHE]['enabled'] && interface_exists(
            CacheItemPoolInterface::class
        )) {
            $container->register(SearchResultCacheOrchestrator::class, SearchResultCacheOrchestrator::class)
                ->setArguments([
                    new Reference(sprintf('cache.%s', $configuration[self::CACHE]['pool'])),
                    new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ])
                ->setPublic(false)
                ->addTag('monolog.logger', [
                    'channel' => 'meilisearch',
                ])
                ->addTag('container.preload', [
                    'class' => SearchResultCacheOrchestrator::class,
                ]);

            $container->register(ClearSearchResultCacheCommand::class, ClearSearchResultCacheCommand::class)
                ->setArguments([
                    new Reference(
                        SearchResultCacheOrchestrator::class,
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                    ),
                ])
                ->setPublic(false)
                ->addTag('console.command')
                ->addTag('container.preload', [
                    'class' => ClearSearchResultCacheCommand::class,
                ]);
        }

        $container->register(DeleteIndexCommand::class, DeleteIndexCommand::class)
            ->setArguments([
                new Reference(IndexSynchronizerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => DeleteIndexCommand::class,
            ]);

        $container->register(ListIndexesCommand::class, ListIndexesCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => ListIndexesCommand::class,
            ]);

        $container->register(WarmDocumentsCommand::class, WarmDocumentsCommand::class)
            ->setArguments([
                new Reference(DocumentLoader::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => WarmDocumentsCommand::class,
            ]);

        $container->register(WarmIndexesCommand::class, WarmIndexesCommand::class)
            ->setArguments([
                $configuration['indexes'],
                new Reference(IndexSynchronizerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                $configuration['prefix'],
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => WarmIndexesCommand::class,
            ]);

        $container->register(UpdateIndexesCommand::class, UpdateIndexesCommand::class)
            ->setArguments([
                $configuration['indexes'],
                new Reference(IndexSynchronizerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                $configuration['prefix'],
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => UpdateIndexesCommand::class,
            ]);

        $container->register(MigrateDocumentsCommand::class, MigrateDocumentsCommand::class)
            ->setArguments([
                new Reference(
                    DocumentMigrationOrchestratorInterface::class,
                    ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE
                ),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => MigrateDocumentsCommand::class,
            ]);
    }

    private function registerHealthEntryPoint(ContainerBuilder $container): void
    {
        $container->register(HealthEntryPoint::class, HealthEntryPoint::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => HealthEntryPoint::class,
            ]);

        $container->setAlias(HealthEntryPointInterface::class, HealthEntryPoint::class);
    }

    private function registerDataCollector(ContainerBuilder $container): void
    {
        $container->register(MeiliSearchBundleDataCollector::class, MeiliSearchBundleDataCollector::class)
            ->setArguments([
                new Reference(SearchEventListInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('data_collector', [
                'template' => '@MeiliSearch/Collector/data_collector.html.twig',
                'id' => MeiliSearchBundleDataCollector::NAME,
            ])
            ->addTag('container.preload', [
                'class' => MeiliSearchBundleDataCollector::class,
            ]);
    }
}
