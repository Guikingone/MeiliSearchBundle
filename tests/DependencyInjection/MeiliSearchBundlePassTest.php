<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\Document\DocumentEntryPoint;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Search\SearchEntryPoint;
use MeiliSearchBundle\DependencyInjection\MeiliSearchBundlePass;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundlePassTest extends TestCase
{
    public function testPassCanBeProcessed(): void
    {
        $container = $this->getContainer();

        (new MeiliSearchBundlePass())->process($container);

        static::assertTrue($container->has('.debug.'.TraceableIndexOrchestrator::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition('.debug.'.TraceableIndexOrchestrator::class)->getArgument(0));
        static::assertSame(IndexOrchestratorInterface::class, $container->getDefinition('.debug.'.TraceableIndexOrchestrator::class)->getDecoratedService()[0]);

        static::assertTrue($container->has('.debug.'.TraceableDocumentEntryPoint::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition('.debug.'.TraceableDocumentEntryPoint::class)->getArgument(0));
        static::assertSame(DocumentEntryPointInterface::class, $container->getDefinition('.debug.'.TraceableDocumentEntryPoint::class)->getDecoratedService()[0]);

        static::assertTrue($container->has('.debug.'.TraceableSearchEntryPoint::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition('.debug.'.TraceableSearchEntryPoint::class)->getArgument(0));
        static::assertSame(SearchEntryPointInterface::class, $container->getDefinition('.debug.'.TraceableSearchEntryPoint::class)->getDecoratedService()[0]);

        static::assertTrue($container->has('.debug.'.TraceableSynonymsOrchestrator::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition('.debug.'.TraceableSynonymsOrchestrator::class)->getArgument(0));
        static::assertSame(SynonymsOrchestratorInterface::class, $container->getDefinition('.debug.'.TraceableSynonymsOrchestrator::class)->getDecoratedService()[0]);

        static::assertTrue($container->has('.debug.'.TraceableUpdateOrchestrator::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition('.debug.'.TraceableUpdateOrchestrator::class)->getArgument(0));
        static::assertSame(UpdateOrchestratorInterface::class, $container->getDefinition('.debug.'.TraceableUpdateOrchestrator::class)->getDecoratedService()[0]);

        static::assertTrue($container->has(MeiliSearchBundleDataCollector::class));
        static::assertInstanceOf(Reference::class, $container->getDefinition(MeiliSearchBundleDataCollector::class)->getArgument(0));
        static::assertInstanceOf(Reference::class, $container->getDefinition(MeiliSearchBundleDataCollector::class)->getArgument(1));
        static::assertInstanceOf(Reference::class, $container->getDefinition(MeiliSearchBundleDataCollector::class)->getArgument(2));
        static::assertInstanceOf(Reference::class, $container->getDefinition(MeiliSearchBundleDataCollector::class)->getArgument(3));
        static::assertTrue($container->getDefinition(MeiliSearchBundleDataCollector::class)->hasTag('data_collector'));
        static::assertArrayHasKey('template', $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('data_collector')[0]);
        static::assertArrayHasKey('id', $container->getDefinition(MeiliSearchBundleDataCollector::class)->getTag('data_collector')[0]);
    }

    private function getContainer(): ContainerBuilder
    {
        $client = $this->createMock(Client::class);

        $container = new ContainerBuilder();
        $container->setDefinition(IndexOrchestrator::class, (new Definition(IndexOrchestrator::class, [
            $client,
        ])));
        $container->setAlias(IndexOrchestratorInterface::class, IndexOrchestrator::class);

        $container->setDefinition(DocumentEntryPoint::class, (new Definition(DocumentEntryPoint::class, [
            $client,
        ])));
        $container->setAlias(DocumentEntryPointInterface::class, DocumentEntryPoint::class);

        $container->setDefinition(SynonymsOrchestrator::class, (new Definition(SynonymsOrchestrator::class, [
            new Reference(IndexOrchestratorInterface::class),
        ])));
        $container->setAlias(SynonymsOrchestratorInterface::class, SynonymsOrchestrator::class);

        $container->setDefinition(SearchEntryPoint::class, (new Definition(SearchEntryPoint::class, [
            new Reference(IndexOrchestratorInterface::class),
        ])));
        $container->setAlias(SearchEntryPointInterface::class, SearchEntryPoint::class);

        $container->setDefinition(UpdateOrchestrator::class, (new Definition(UpdateOrchestrator::class, [
            $client,
        ])));
        $container->setAlias(UpdateOrchestratorInterface::class, UpdateOrchestrator::class);

        return $container;
    }
}
