<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearch\Client;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Cache\SearchResultCacheOrchestrator;
use MeiliSearchBundle\Command\ClearSearchResultCacheCommand;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Command\ListIndexesCommand;
use MeiliSearchBundle\Command\WarmDocumentsCommand;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\Document\DocumentLoader;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\EventSubscriber\DocumentEventSubscriber;
use MeiliSearchBundle\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\EventSubscriber\IndexEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SearchEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SettingsEventSubscriber;
use MeiliSearchBundle\EventSubscriber\SynonymsEventSubscriber;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexSettingsOrchestrator;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Loader\LoaderInterface;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\DependencyInjection\MeiliSearchExtension;
use MeiliSearchBundle\Messenger\Handler\AddIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\DeleteIndexMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\AddDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\DeleteDocumentMessageHandler;
use MeiliSearchBundle\Messenger\Handler\Document\UpdateDocumentMessageHandler;
use MeiliSearchBundle\Result\ResultBuilder;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use MeiliSearchBundle\Search\SearchEntryPoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Bridge\Doctrine\Serializer\DocumentNormalizer;
use MeiliSearchBundle\Twig\SearchExtension;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchExtensionTest extends TestCase
{
    public function testDefinitionsAreRegistered(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition('annotation_reader', new Definition(AnnotationReader::class));
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(Client::class));
        static::assertTrue($container->getDefinition(Client::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(Client::class)->getArguments());
        static::assertSame('http://127.0.0.1', $container->getDefinition(Client::class)->getArgument(0));
        static::assertNull($container->getDefinition(Client::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(Client::class)->getArgument(2));
        static::assertArrayHasKey('class', $container->getDefinition(Client::class)->getTag('container.preload')[0]);
        static::assertSame(Client::class, $container->getDefinition(Client::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(ResultBuilder::class));
        static::assertTrue($container->hasAlias(ResultBuilderInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ResultBuilder::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ResultBuilder::class)->getArgument(1));
        static::assertTrue($container->getDefinition(ResultBuilder::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(ResultBuilder::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(ResultBuilder::class)->getTag('container.preload')[0]);
        static::assertSame(ResultBuilder::class, $container->getDefinition(ResultBuilder::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(IndexOrchestrator::class));
        static::assertTrue($container->hasAlias(IndexOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexOrchestrator::class)->getArgument(1));
        static::assertTrue($container->getDefinition(IndexOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(IndexOrchestrator::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(IndexOrchestrator::class)->getTag('container.preload')[0]);
        static::assertSame(IndexOrchestrator::class, $container->getDefinition(IndexOrchestrator::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(IndexSettingsOrchestrator::class));
        static::assertTrue($container->hasAlias(IndexSettingsOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexSettingsOrchestrator::class)->getArgument(2));
        static::assertTrue($container->getDefinition(IndexSettingsOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(IndexSettingsOrchestrator::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(IndexSettingsOrchestrator::class)->getTag('container.preload')[0]);
        static::assertSame(IndexSettingsOrchestrator::class, $container->getDefinition(IndexSettingsOrchestrator::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DocumentEntryPoint::class));
        static::assertTrue($container->hasAlias(DocumentEntryPointInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentEntryPoint::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentEntryPoint::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentEntryPoint::class)->getArgument(2));
        static::assertTrue($container->getDefinition(DocumentEntryPoint::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(DocumentEntryPoint::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(DocumentEntryPoint::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentEntryPoint::class, $container->getDefinition(DocumentEntryPoint::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(SynonymsOrchestrator::class));
        static::assertTrue($container->hasAlias(SynonymsOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SynonymsOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SynonymsOrchestrator::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SynonymsOrchestrator::class)->getArgument(2));
        static::assertTrue($container->getDefinition(SynonymsOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(SynonymsOrchestrator::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(SynonymsOrchestrator::class)->getTag('container.preload')[0]);
        static::assertSame(SynonymsOrchestrator::class, $container->getDefinition(SynonymsOrchestrator::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(UpdateOrchestrator::class));
        static::assertTrue($container->hasAlias(UpdateOrchestratorInterface::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(UpdateOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(UpdateOrchestrator::class)->getArgument(1));
        static::assertTrue($container->getDefinition(UpdateOrchestrator::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(UpdateOrchestrator::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(UpdateOrchestrator::class)->getTag('container.preload')[0]);
        static::assertSame(UpdateOrchestrator::class, $container->getDefinition(UpdateOrchestrator::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DocumentLoader::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentLoader::class)->getArgument(0));
        static::assertInstanceOf(TaggedIteratorArgument::class, $container->getDefinition(DocumentLoader::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentLoader::class)->getArgument(2));
        static::assertTrue($container->getDefinition(DocumentLoader::class)->hasTag('container.preload'));
        static::assertTrue($container->getDefinition(DocumentLoader::class)->hasTag('meili_search.document_loader'));
        static::assertNotEmpty($container->getDefinition(DocumentLoader::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(DocumentLoader::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentLoader::class, $container->getDefinition(DocumentLoader::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DocumentReader::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentReader::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DocumentReader::class)->hasTag('meili_search.annotation_reader'));
        static::assertTrue($container->getDefinition(DocumentReader::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DocumentReader::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentReader::class, $container->getDefinition(DocumentReader::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DocumentSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(2));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(3));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(4));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentSubscriber::class)->getArgument(5));
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('container.preload'));
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('doctrine.event_subscriber'));
        static::assertTrue($container->getDefinition(DocumentSubscriber::class)->hasTag('meili_search.subscriber'));
        static::assertNotEmpty($container->getDefinition(DocumentSubscriber::class)->getArguments());
        static::assertArrayHasKey('class', $container->getDefinition(DocumentSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentSubscriber::class, $container->getDefinition(DocumentSubscriber::class)->getTag('container.preload')[0]['class']);

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
    }

    public function testClientCanBeConfigured(): void
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
        static::assertTrue($container->getDefinition(Client::class)->hasTag('container.preload'));
        static::assertNotEmpty($container->getDefinition(Client::class)->getArguments());
        static::assertSame('http://127.0.0.1:7700', $container->getDefinition(Client::class)->getArgument(0));
        static::assertSame('test', $container->getDefinition(Client::class)->getArgument(1));
    }

    public function testCommandsAreConfiguredWithCache(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition('annotations_reader', new Definition(AnnotationReader::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([
            'meili_search' => [
                'cache' => [
                    'enabled' => true,
                    'pool' => 'app',
                ],
            ],
        ], $container);

        static::assertTrue($container->hasDefinition(SearchResultCacheOrchestrator::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchResultCacheOrchestrator::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchResultCacheOrchestrator::class)->getArgument(1));
        static::assertTrue($container->getDefinition(SearchResultCacheOrchestrator::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('container.preload')[0]);
        static::assertSame(SearchResultCacheOrchestrator::class, $container->getDefinition(SearchResultCacheOrchestrator::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(ClearSearchResultCacheCommand::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ClearSearchResultCacheCommand::class)->getArgument(0));
        static::assertTrue($container->getDefinition(ClearSearchResultCacheCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(ClearSearchResultCacheCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(ClearSearchResultCacheCommand::class)->getTag('container.preload')[0]);
        static::assertSame(ClearSearchResultCacheCommand::class, $container->getDefinition(ClearSearchResultCacheCommand::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DeleteIndexCommand::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DeleteIndexCommand::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DeleteIndexCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(DeleteIndexCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DeleteIndexCommand::class)->getTag('container.preload')[0]);
        static::assertSame(DeleteIndexCommand::class, $container->getDefinition(DeleteIndexCommand::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(ListIndexesCommand::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ListIndexesCommand::class)->getArgument(0));
        static::assertTrue($container->getDefinition(ListIndexesCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(ListIndexesCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(ListIndexesCommand::class)->getTag('container.preload')[0]);
        static::assertSame(ListIndexesCommand::class, $container->getDefinition(ListIndexesCommand::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(WarmDocumentsCommand::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(WarmDocumentsCommand::class)->getArgument(0));
        static::assertTrue($container->getDefinition(WarmDocumentsCommand::class)->hasTag('console.command'));
        static::assertTrue($container->getDefinition(WarmDocumentsCommand::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(WarmDocumentsCommand::class)->getTag('container.preload')[0]);
        static::assertSame(WarmDocumentsCommand::class, $container->getDefinition(WarmDocumentsCommand::class)->getTag('container.preload')[0]['class']);
    }

    public function testCommandsAreConfiguredWithoutCache(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition('annotations_reader', new Definition(AnnotationReader::class));
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
        $container->setDefinition('annotation_reader', new Definition(AnnotationReader::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(DocumentNormalizer::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentNormalizer::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentNormalizer::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentNormalizer::class)->getArgument(2));
        static::assertTrue($container->getDefinition(DocumentNormalizer::class)->hasTag('serializer.normalizer'));
        static::assertTrue($container->getDefinition(DocumentNormalizer::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DocumentNormalizer::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentNormalizer::class, $container->getDefinition(DocumentNormalizer::class)->getTag('container.preload')[0]['class']);
    }

    public function testMessengerHandlersAreConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $container->setDefinition('annotations_reader', new Definition(AnnotationReader::class));
        $container->setDefinition(MessageBusInterface::class, new Definition(MessageBusInterface::class));
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(AddIndexMessageHandler::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(AddIndexMessageHandler::class)->getArgument(0));
        static::assertTrue($container->getDefinition(AddIndexMessageHandler::class)->hasTag('messenger.message_handler'));
        static::assertTrue($container->getDefinition(AddIndexMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(AddIndexMessageHandler::class)->getTag('container.preload')[0]);
        static::assertSame(AddIndexMessageHandler::class, $container->getDefinition(AddIndexMessageHandler::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DeleteIndexMessageHandler::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DeleteIndexMessageHandler::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DeleteIndexMessageHandler::class)->hasTag('messenger.message_handler'));
        static::assertTrue($container->getDefinition(DeleteIndexMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DeleteIndexMessageHandler::class)->getTag('container.preload')[0]);
        static::assertSame(DeleteIndexMessageHandler::class, $container->getDefinition(DeleteIndexMessageHandler::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(AddDocumentMessageHandler::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(AddDocumentMessageHandler::class)->getArgument(0));
        static::assertTrue($container->getDefinition(AddDocumentMessageHandler::class)->hasTag('messenger.message_handler'));
        static::assertTrue($container->getDefinition(AddDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(AddDocumentMessageHandler::class)->getTag('container.preload')[0]);
        static::assertSame(AddDocumentMessageHandler::class, $container->getDefinition(AddDocumentMessageHandler::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(DeleteDocumentMessageHandler::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DeleteDocumentMessageHandler::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DeleteDocumentMessageHandler::class)->hasTag('messenger.message_handler'));
        static::assertTrue($container->getDefinition(DeleteDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DeleteDocumentMessageHandler::class)->getTag('container.preload')[0]);
        static::assertSame(DeleteDocumentMessageHandler::class, $container->getDefinition(DeleteDocumentMessageHandler::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(UpdateDocumentMessageHandler::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(UpdateDocumentMessageHandler::class)->getArgument(0));
        static::assertTrue($container->getDefinition(UpdateDocumentMessageHandler::class)->hasTag('messenger.message_handler'));
        static::assertTrue($container->getDefinition(UpdateDocumentMessageHandler::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(UpdateDocumentMessageHandler::class)->getTag('container.preload')[0]);
        static::assertSame(UpdateDocumentMessageHandler::class, $container->getDefinition(UpdateDocumentMessageHandler::class)->getTag('container.preload')[0]['class']);
    }

    public function testTwigIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(SearchExtension::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchExtension::class)->getArgument(0));
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('twig.extension'));
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('twig.runtime'));
        static::assertTrue($container->getDefinition(SearchExtension::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SearchExtension::class)->getTag('container.preload')[0]);
        static::assertSame(SearchExtension::class, $container->getDefinition(SearchExtension::class)->getTag('container.preload')[0]['class']);
    }

    public function testSearchEntryPointIsConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(SearchEntryPoint::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(2));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEntryPoint::class)->getArgument(3));
        static::assertTrue($container->getDefinition(SearchEntryPoint::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SearchEntryPoint::class)->getTag('container.preload')[0]);
        static::assertSame(SearchEntryPoint::class, $container->getDefinition(SearchEntryPoint::class)->getTag('container.preload')[0]['class']);
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
        static::assertArrayHasKey(SearchEntryPointInterface::class, $container->getAutoconfiguredInstanceof());
    }

    public function testSubscribersAreConfigured(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition(DocumentEventSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(DocumentEventSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(DocumentEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(DocumentEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(DocumentEventSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(DocumentEventSubscriber::class, $container->getDefinition(DocumentEventSubscriber::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(ExceptionSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(ExceptionSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(ExceptionSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(ExceptionSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(ExceptionSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(ExceptionSubscriber::class, $container->getDefinition(ExceptionSubscriber::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(IndexEventSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(IndexEventSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(IndexEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(IndexEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(IndexEventSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(IndexEventSubscriber::class, $container->getDefinition(IndexEventSubscriber::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(SearchEventSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SearchEventSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(SearchEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(SearchEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SearchEventSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(SearchEventSubscriber::class, $container->getDefinition(SearchEventSubscriber::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(SettingsEventSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SettingsEventSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(SettingsEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(SettingsEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SettingsEventSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(SettingsEventSubscriber::class, $container->getDefinition(SettingsEventSubscriber::class)->getTag('container.preload')[0]['class']);

        static::assertTrue($container->hasDefinition(SynonymsEventSubscriber::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(SynonymsEventSubscriber::class)->getArgument(0));
        static::assertTrue($container->getDefinition(SynonymsEventSubscriber::class)->hasTag('kernel.event_subscriber'));
        static::assertTrue($container->getDefinition(SynonymsEventSubscriber::class)->hasTag('container.preload'));
        static::assertArrayHasKey('class', $container->getDefinition(SynonymsEventSubscriber::class)->getTag('container.preload')[0]);
        static::assertSame(SynonymsEventSubscriber::class, $container->getDefinition(SynonymsEventSubscriber::class)->getTag('container.preload')[0]['class']);
    }
}
