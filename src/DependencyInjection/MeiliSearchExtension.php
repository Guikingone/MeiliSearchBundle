<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidDenormalizer;
use MeiliSearchBundle\Bridge\RamseyUuid\Serializer\UuidNormalizer;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Command\ClearSearchResultCacheCommand;
use MeiliSearchBundle\Command\DeleteIndexesCommand;
use MeiliSearchBundle\Command\WarmIndexesCommand;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;
use MeiliSearchBundle\Document\DocumentLoader;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\EventSubscriber\DocumentEventSubscriber;
use MeiliSearchBundle\EventSubscriber\IndexEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SearchEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SettingsEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SynonymsEventSubscriber;
use MeiliSearchBundle\Form\Type\MeiliSearchChoiceType;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Command\ListIndexesCommand;
use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\Index\IndexSettingsOrchestrator;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableIndexSettingsOrchestrator;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Loader\LoaderInterface;
use MeiliSearchBundle\Messenger\Handler\AddIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\DeleteIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\AddDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\DeleteDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\UpdateDocumentMessageHandler;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Result\ResultBuilder;
use MeiliSearchBundle\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use MeiliSearchBundle\Search\CachedSearchEntryPoint;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use MeiliSearchBundle\Twig\SearchExtension;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
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
    private const DEBUG = '.debug.';
    private const INNER = '.inner';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $this->registerClientAndMetadataRegistry($container, $config);
        $this->registerResultBuilder($container);
        $this->registerOrchestrator($container);
        $this->registerLoaders($container);
        $this->registerForm($container);
        $this->registerDoctrineSubscribers($container);
        $this->registerSerializer($container);
        $this->registerMessengerHandler($container);
        $this->registerTwig($container);
        $this->registerSearchEntryPoint($container, $config);
        $this->registerSubscribers($container);
        $this->registerCommands($container, $config);

        $this->registerTraceableIndexOrchestrator($container);
        $this->registerTraceableIndexSettingsOrchestrator($container);
        $this->registerTraceableDocumentOrchestrator($container);
        $this->registerTraceableSearchEntryPoint($container);
        $this->registerTraceableSynonymsOrchestrator($container);
        $this->registerTraceableUpdateOrchestrator($container);
        $this->registerDataCollector($container);

        $container->registerForAutoconfiguration(DocumentDataProviderInterface::class)
            ->addTag(self::DOCUMENT_PROVIDER_IDENTIFIER)
        ;
        $container->registerForAutoconfiguration(TraceableDataCollectorInterface::class)
            ->addTag('meili_search.data_collector.traceable')
        ;
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
            ])
        ;

        $container->register(IndexMetadataRegistry::class, IndexMetadataRegistry::class)
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexMetadataRegistry::class,
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
            ->setPublic(false)
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
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => IndexOrchestrator::class,
            ])
        ;
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
            ])
        ;
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
            ])
        ;
        $container->setAlias(DocumentEntryPointInterface::class, DocumentEntryPoint::class);

        $container->register(SynonymsOrchestrator::class, SynonymsOrchestrator::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(EventDispatcherInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => SynonymsOrchestrator::class,
            ])
        ;
        $container->setAlias(SynonymsOrchestratorInterface::class, SynonymsOrchestrator::class);

        $container->register(UpdateOrchestrator::class, UpdateOrchestrator::class)
            ->setArguments([
                new Reference(Client::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
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
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new TaggedIteratorArgument(self::DOCUMENT_PROVIDER_IDENTIFIER),
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag(self::LOADER_LIST['document'])
            ->addTag('container.preload', [
                'class' => DocumentLoader::class,
            ])
        ;

        $container->registerForAutoconfiguration(LoaderInterface::class)->addTag(self::LOADER_TAG);
    }

    private function registerForm(ContainerBuilder $container): void
    {
        if (!$container->has(FormFactoryInterface::class)) {
            return;
        }

        $container->register(MeiliSearchChoiceType::class, MeiliSearchChoiceType::class)
            ->setArguments([
                new Reference(SearchEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('form.type')
            ->addTag('container.preload', [
                'class' => MeiliSearchChoiceType::class,
            ])
        ;
    }

    private function registerDoctrineSubscribers(ContainerBuilder $container): void
    {
        if (!$container->has('annotation_reader')) {
            return;
        }

        $container->register(DocumentReader::class, DocumentReader::class)
            ->setArguments([
                new Reference('annotation_reader', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag(self::ANNOTATION_READER_TAG)
            ->addTag('container.preload', [
                'class' => DocumentReader::class,
            ])
        ;

        $container->register(DocumentSubscriber::class, DocumentSubscriber::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(DocumentReader::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(IndexMetadataRegistry::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference('property_accessor', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(SerializerInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(MessageBusInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('meili_search.subscriber')
            ->addTag('doctrine.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentSubscriber::class,
            ])
        ;
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
            ])
        ;

        if (interface_exists(UuidInterface::class)) {
            $container->register(UuidNormalizer::class, UuidNormalizer::class)
                ->setPublic(false)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidNormalizer::class,
                ])
            ;

            $container->register(UuidDenormalizer::class, UuidDenormalizer::class)
                ->setPublic(false)
                ->addTag('serializer.normalizer')
                ->addTag('container.preload', [
                    'class' => UuidDenormalizer::class,
                ])
            ;
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
            ])
            ->setPublic(false)
            ->addTag('container.preload', [
                'class' => SearchEntryPoint::class,
            ])
        ;
        $container->setAlias(SearchEntryPointInterface::class, SearchEntryPoint::class);

        if (array_key_exists('cache', $configuration) && $configuration['cache']['enabled'] && interface_exists(CacheItemPoolInterface::class)) {
            $container->register(SearchResultCacheOrchestrator::class, SearchResultCacheOrchestrator::class)
                ->setArguments([
                    new Reference(CacheItemPoolInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                    new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ])
                ->setPublic(false)
                ->addTag('container.preload', [
                    'class' => SearchResultCacheOrchestrator::class,
                ])
            ;

            $container->register(CachedSearchEntryPoint::class, CachedSearchEntryPoint::class)
                ->setArguments([
                    new Reference(SearchResultCacheOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                    new Reference(SearchEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                ])
                ->setPublic(false)
                ->addTag('container.preload', [
                    'class' => CachedSearchEntryPoint::class,
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
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddIndexMessageHandler::class,
            ])
        ;

        $container->register(DeleteIndexMessageHandler::class, DeleteIndexMessageHandler::class)
            ->setArguments([
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteIndexMessageHandler::class,
            ])
        ;

        $container->register(AddDocumentMessageHandler::class, AddDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => AddDocumentMessageHandler::class,
            ])
        ;

        $container->register(DeleteDocumentMessageHandler::class, DeleteDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('messenger.message_handler')
            ->addTag('container.preload', [
                'class' => DeleteDocumentMessageHandler::class,
            ])
        ;

        $container->register(UpdateDocumentMessageHandler::class, UpdateDocumentMessageHandler::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
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
                new Reference(SearchEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
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
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => DocumentEventSubscriber::class,
            ])
        ;

        $container->register(ExceptionSubscriber::class, ExceptionSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => ExceptionSubscriber::class,
            ])
        ;

        $container->register(IndexEventSubscriber::class, IndexEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => IndexEventSubscriber::class,
            ])
        ;

        $container->register(SearchEventSubscriber::class, SearchEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SearchEventSubscriber::class,
            ])
        ;

        $container->register(SettingsEventSubscriber::class, SettingsEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SettingsEventSubscriber::class,
            ])
        ;

        $container->register(SynonymsEventSubscriber::class, SynonymsEventSubscriber::class)
            ->setArguments([
                new Reference(LoggerInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('container.preload', [
                'class' => SynonymsEventSubscriber::class,
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
                ->setPublic(false)
                ->addTag('container.preload', [
                    'class' => SearchResultCacheOrchestrator::class,
                ])
            ;

            $container->register(ClearSearchResultCacheCommand::class, ClearSearchResultCacheCommand::class)
                ->setArguments([
                    new Reference(SearchResultCacheOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                ])
            ->setPublic(false)
                ->addTag('console.command')
                ->addTag('container.preload', [
                    'class' => ClearSearchResultCacheCommand::class,
                ])
            ;
        }

        $container->register(DeleteIndexCommand::class, DeleteIndexCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => DeleteIndexCommand::class,
            ])
        ;

        $container->register(DeleteIndexesCommand::class, DeleteIndexesCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => DeleteIndexesCommand::class,
            ])
        ;

        $container->register(ListIndexesCommand::class, ListIndexesCommand::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => ListIndexesCommand::class,
            ])
        ;

        $container->register(WarmDocumentsCommand::class, WarmDocumentsCommand::class)
            ->setArguments([
                new Reference(DocumentLoader::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => WarmDocumentsCommand::class,
            ])
        ;

        $container->register(WarmIndexesCommand::class, WarmIndexesCommand::class)
            ->setArguments([
                $configuration['indexes'],
                new Reference(IndexMetadataRegistry::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(IndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(MessageBusInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE),
                $configuration['prefix'],
            ])
            ->setPublic(false)
            ->addTag('console.command')
            ->addTag('container.preload', [
                'class' => WarmIndexesCommand::class,
            ])
        ;
    }

    private function registerTraceableIndexOrchestrator(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(IndexOrchestratorInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableIndexOrchestrator::class, TraceableIndexOrchestrator::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableIndexOrchestrator::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(IndexOrchestratorInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerTraceableIndexSettingsOrchestrator(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(IndexSettingsOrchestratorInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableIndexSettingsOrchestrator::class, TraceableIndexSettingsOrchestrator::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableIndexSettingsOrchestrator::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(IndexSettingsOrchestratorInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerTraceableDocumentOrchestrator(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(DocumentEntryPointInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableDocumentEntryPoint::class, TraceableDocumentEntryPoint::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableDocumentEntryPoint::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(DocumentEntryPointInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerTraceableSearchEntryPoint(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(SearchEntryPointInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableSearchEntryPoint::class, TraceableSearchEntryPoint::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableSearchEntryPoint::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(SearchEntryPointInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerTraceableSynonymsOrchestrator(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(SynonymsOrchestratorInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableSynonymsOrchestrator::class, TraceableSynonymsOrchestrator::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableSynonymsOrchestrator::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(SynonymsOrchestratorInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerTraceableUpdateOrchestrator(ContainerBuilder $container): void
    {
        if (!$container->hasAlias(UpdateOrchestratorInterface::class)) {
            return;
        }

        $container->register(self::DEBUG.TraceableUpdateOrchestrator::class, TraceableUpdateOrchestrator::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableUpdateOrchestrator::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(UpdateOrchestratorInterface::class)
            ->setPublic(false)
        ;
    }

    private function registerDataCollector(ContainerBuilder $container): void
    {
        $container->register(MeiliSearchBundleDataCollector::class, MeiliSearchBundleDataCollector::class)
            ->setArguments([
                new Reference(self::DEBUG.TraceableIndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(self::DEBUG.TraceableIndexSettingsOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(self::DEBUG.TraceableDocumentEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(self::DEBUG.TraceableSearchEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(self::DEBUG.TraceableSynonymsOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setPublic(false)
            ->addTag('data_collector', [
                'template' => '@MeiliSearch/Collector/data_collector.html.twig',
                'id'       => 'meilisearch',
            ])
        ;
    }
}
