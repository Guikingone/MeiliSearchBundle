<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Command\ClearSearchResultCacheCommand;
use MeiliSearchBundle\Document\DocumentLoader;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\EventSubscriber\DocumentEventSubscriber;
use MeiliSearchBundle\EventSubscriber\IndexEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SearchEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SettingsEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SynonymsEventSubscriber;
use MeiliSearchBundle\Exception\InvalidIndexConfigurationException;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Command\ListIndexesCommand;
use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\Index\IndexSettingsOrchestrator;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Loader\LoaderInterface;
use MeiliSearchBundle\Messenger\Handler\AddIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\DeleteIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\AddDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\DeleteDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\UpdateDocumentMessageHandler;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Result\ResultBuilder;
use MeiliSearchBundle\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use MeiliSearchBundle\Search\CachedSearchEntryPoint;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Twig\SearchExtension;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use MeiliSearchBundle\Search\SearchEntryPoint;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use function array_key_exists;
use function interface_exists;

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

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->registerClient($container, $config);
        $this->registerResultBuilder($container);
        $this->registerOrchestrator($container);
        $this->registerLoaders($container);
        $this->registerDoctrineSubscribers($container);
        $this->registerSerializer($container);
        $this->registerMessengerHandler($container);
        $this->registerTwig($container);
        $this->configureIndexes($container, $config);
        $this->registerSearchEntryPoint($container, $config);
        $this->registerSubscribers($container);
        $this->registerCommands($container, $config);

        $container->registerForAutoconfiguration(DocumentDataProviderInterface::class)
            ->addTag(self::DOCUMENT_PROVIDER_IDENTIFIER)
        ;
    }

    private function registerClient(ContainerBuilder $container, array $configuration): void
    {
        $container->register(Client::class, Client::class)
            ->setArguments([
                $configuration['host'],
                $configuration['apiKey'] ?? null,
                new Reference('http_client', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => Client::class,
            ])
        ;
    }

    private function registerResultBuilder(ContainerBuilder $container): void
    {
        $container->register(ResultBuilder::class, ResultBuilder::class)
            ->setArguments([
                new Reference(SerializerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => ResultBuilder::class,
            ])
        ;
        $container->setAlias(ResultBuilderInterface::class, ResultBuilder::class);
    }

    private function registerOrchestrator(ContainerBuilder $container): void
    {
        $container->register(IndexOrchestrator::class, IndexOrchestrator::class)
            ->setArguments([
                new Reference(Client::class),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => IndexOrchestrator::class,
            ])
        ;
        $container->setAlias(IndexOrchestratorInterface::class, IndexOrchestrator::class);

        $container->register(IndexSettingsOrchestrator::class, IndexSettingsOrchestrator::class)
            ->setArguments([
                new Reference(Client::class),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => IndexSettingsOrchestrator::class,
            ])
        ;
        $container->setAlias(IndexSettingsOrchestratorInterface::class, IndexSettingsOrchestrator::class);

        $container->register(DocumentEntryPoint::class, DocumentEntryPoint::class)
            ->setArguments([
                new Reference(Client::class),
                new Reference(ResultBuilderInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => DocumentEntryPoint::class,
            ])
        ;
        $container->setAlias(DocumentEntryPointInterface::class, DocumentEntryPoint::class);

        $container->register(SynonymsOrchestrator::class, SynonymsOrchestrator::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => SynonymsOrchestrator::class,
            ])
        ;
        $container->setAlias(SynonymsOrchestratorInterface::class, SynonymsOrchestrator::class);

        $container->register(UpdateOrchestrator::class, UpdateOrchestrator::class)
            ->setArguments([
                new Reference(Client::class),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => UpdateOrchestrator::class,
            ])
        ;
        $container->setAlias(UpdateOrchestratorInterface::class, 'meili_search.update_orchestrator');
    }

    private function registerLoaders(ContainerBuilder $container): void
    {
        $container->register(DocumentLoader::class, DocumentLoader::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class),
                new TaggedIteratorArgument(self::DOCUMENT_PROVIDER_IDENTIFIER),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag(self::LOADER_LIST['document'])
            ->addTag('container.preload', [
                'class' => DocumentLoader::class,
            ])
        ;

        $container->registerForAutoconfiguration(LoaderInterface::class)->addTag(self::LOADER_TAG);
    }

    private function registerDoctrineSubscribers(ContainerBuilder $container): void
    {
        if (!$container->has('annotations.reader')) {
            return;
        }

        $container->register(DocumentReader::class, DocumentReader::class)
            ->setArguments([
                new Reference('annotations.reader', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->addTag(self::ANNOTATION_READER_TAG)
            ->addTag('container.preload', [
                'class' => DocumentReader::class,
            ])
        ;

        $container->register(DocumentSubscriber::class, DocumentSubscriber::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class),
                new Reference(DocumentReader::class),
                new Reference(IndexMetadataRegistry::class),
                new Reference('property_accessor'),
                new Reference(SerializerInterface::class),
                new Reference(MessageBusInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.subscriber')
            ->addTag('doctrine.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentSubscriber::class,
            ])
        ;
    }

    private function registerCommands(ContainerBuilder $container, array $configuration): void
    {
        if (array_key_exists('cache', $configuration) && $configuration['cache']['enabled'] && interface_exists(CacheItemPoolInterface::class)) {
            $container->register(SearchResultCacheOrchestrator::class, SearchResultCacheOrchestrator::class)
                ->setArguments([
                    new Reference(sprintf('cache.%s', $configuration['cache']['pool'])),
                    new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ])
                ->addTag('container.preload', [
                    'class' => SearchResultCacheOrchestrator::class,
                ])
            ;

            $container->register(ClearSearchResultCacheCommand::class, ClearSearchResultCacheCommand::class)
                ->setArguments([
                    new Reference(SearchResultCacheOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                ])
                ->addTag('console.command')
                ->addTag('container.preload', [
                    'class' => ClearSearchResultCacheCommand::class,
                ])
            ;
        }

        $container->register(DeleteIndexCommand::class, DeleteIndexCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
            ])
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => DeleteIndexCommand::class,
            ])
        ;

        $container->register(ListIndexesCommand::class, ListIndexesCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
            ])
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => ListIndexesCommand::class,
            ])
        ;

        $container->register(WarmDocumentsCommand::class, WarmDocumentsCommand::class)
            ->setArguments([
                new Reference(self::LOADER_LIST['document']),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => WarmDocumentsCommand::class,
            ])
        ;
    }

    private function registerSerializer(ContainerBuilder $container): void
    {
        $container->register(DocumentNormalizer::class, DocumentNormalizer::class)
            ->setArguments([
                new Reference(DocumentReader::class),
                new Reference('serializer.object_normalizer'),
                new Reference('property_accessor'),
            ])
            ->addTag('serializer.normalizer')
            ->addTag('container.preload', [
                'class' => DocumentNormalizer::class,
            ])
        ;

        if (interface_exists(UuidInterface::class)) {
            $container->register(UuidNormalizer::class, UuidNormalizer::class)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidNormalizer::class,
                ])
            ;

            $container->register(UuidDenormalizer::class, UuidDenormalizer::class)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidDenormalizer::class,
                ])
            ;
        }
    }

    private function configureIndexes(ContainerBuilder $container, array $configuration): void
    {
        $container->register(IndexMetadataRegistry::class, IndexMetadataRegistry::class);

        foreach ($configuration['indexes'] as $name => $index) {
            if ($index['async'] && !$container->has(MessageBusInterface::class)) {
                throw new InvalidIndexConfigurationException('The "async" option in index configuration requires a message bus, consider using "composer require symfony/messenger"');
            }

            $indexName = null !== $configuration['prefix'] ? sprintf('%s_%s', $configuration['prefix'], $name) : $name;

            $container->getDefinition(IndexOrchestratorInterface::class)
                ->addMethodCall('addIndex', [
                    $indexName,
                    $index['primaryKey'],
                ])
            ;

            $container->getDefinition(IndexMetadataRegistry::class)->addMethodCall('add', [
                $indexName,
                new IndexMetadata(
                    $indexName,
                    $index['async'],
                    $index['primaryKey'],
                    $index['rankingRules'],
                    $index['stopWords'],
                    $index['acceptNewFields'],
                    $index['distinctAttribute'],
                    $index['facetedAttributes'],
                    $index['searchableAttributes'],
                    $index['displayedAttributes']
                ),
            ]);
        }
    }

    private function registerSearchEntryPoint(ContainerBuilder $container, array $configuration): void
    {
        $container->register(SearchEntryPoint::class, SearchEntryPoint::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
                new Reference(ResultBuilderInterface::class),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('container.preload', [
                'class' => SearchEntryPoint::class,
            ])
        ;

        $container->setAlias(SearchEntryPointInterface::class, SearchEntryPoint::class);

        if (array_key_exists('cache', $configuration) && $configuration['cache']['enabled'] && interface_exists(CacheItemPoolInterface::class)) {
            $container->register(SearchResultCacheOrchestrator::class, SearchResultCacheOrchestrator::class)
                ->setArguments([
                    new Reference(CacheItemPoolInterface::class),
                    new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ])
            ;

            $container->register(CachedSearchEntryPoint::class, CachedSearchEntryPoint::class)
                ->setArguments([
                    new Reference(SearchResultCacheOrchestrator::class),
                    new Reference(SearchEntryPoint::class),
                ])
            ;

            $container->setAlias(SearchEntryPointInterface::class, CachedSearchEntryPoint::class);
        }

        $container->registerForAutoconfiguration(SearchEntryPointInterface::class)
            ->addTag('meili_search.search_entry_point')
        ;
    }

    private function registerMessengerHandler(ContainerBuilder $container): void
    {
        if (!$container->has(MessageBusInterface::class)) {
            return;
        }

        $container->register(AddIndexMessageHandler::class, AddIndexMessageHandler::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
            ])
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddIndexMessageHandler::class,
            ])
        ;

        $container->register(DeleteIndexMessageHandler::class, DeleteIndexMessageHandler::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class)
            ])
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteIndexMessageHandler::class,
            ])
        ;

        $container->register(AddDocumentMessageHandler::class, AddDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class),
            ])
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddDocumentMessageHandler::class,
            ])
        ;

        $container->register(DeleteDocumentMessageHandler::class, DeleteDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class),
            ])
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteDocumentMessageHandler::class,
            ])
        ;

        $container->register(UpdateDocumentMessageHandler::class, UpdateDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class),
            ])
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => UpdateDocumentMessageHandler::class,
            ])
        ;
    }

    private function registerTwig(ContainerBuilder $container): void
    {
        $container->register(SearchExtension::class, SearchExtension::class)
            ->setArguments([
                new Reference(SearchEntryPointInterface::class),
            ])
            ->addTag('twig.extension')
            ->addTag('twig.runtime')
            ->addTag('container.preload', [
                'class' => SearchExtension::class,
            ])
        ;
    }

    private function registerSubscribers(ContainerBuilder $container): void
    {
        $container->register(DocumentEventSubscriber::class, DocumentEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentEventSubscriber::class,
            ])
        ;

        $container->register(ExceptionSubscriber::class, ExceptionSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => ExceptionSubscriber::class,
            ])
        ;

        $container->register(IndexEventSubscriber::class, IndexEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => IndexEventSubscriber::class,
            ])
        ;

        $container->register(SearchEventSubscriber::class, SearchEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SearchEventSubscriber::class,
            ])
        ;

        $container->register(SettingsEventSubscriber::class, SettingsEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SettingsEventSubscriber::class,
            ])
        ;

        $container->register(SynonymsEventSubscriber::class, SynonymsEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SynonymsEventSubscriber::class,
            ])
        ;
    }
}
