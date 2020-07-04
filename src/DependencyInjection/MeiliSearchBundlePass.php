<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableSynonymsOrchestrator;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Search\TraceableSearchEntryPoint;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use MeiliSearchBundle\Update\TraceableUpdateOrchestrator;
use MeiliSearchBundle\Update\UpdateOrchestratorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundlePass implements CompilerPassInterface
{
    private const INNER = 'inner';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->registerTraceableIndexOrchestrator($container);
        $this->registerTraceableDocumentOrchestrator($container);
        $this->registerTraceableSearchEntryPoint($container);
        $this->registerTraceableSynonymsOrchestrator($container);
        $this->registerTraceableUpdateOrchestrator($container);
        $this->registerDataCollector($container);
    }

    private function registerTraceableIndexOrchestrator(ContainerBuilder $container): void
    {
        $container->register(TraceableIndexOrchestrator::class, TraceableIndexOrchestrator::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(IndexOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
        ;
    }

    private function registerTraceableDocumentOrchestrator(ContainerBuilder $container): void
    {
        $container->register(TraceableDocumentEntryPoint::class, TraceableDocumentEntryPoint::class)
            ->setArguments([
                new Reference(DocumentEntryPointInterface::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(DocumentEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
        ;
    }

    private function registerTraceableSearchEntryPoint(ContainerBuilder $container): void
    {
        $container->register(TraceableSearchEntryPoint::class, TraceableSearchEntryPoint::class)
            ->setArguments([
                new Reference(SearchEntryPointInterface::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(SearchEntryPointInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
        ;
    }

    private function registerTraceableSynonymsOrchestrator(ContainerBuilder $container): void
    {
        $container->register(TraceableSynonymsOrchestrator::class, TraceableSynonymsOrchestrator::class)
            ->setArguments([
                new Reference(SynonymsOrchestratorInterface::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(SynonymsOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
        ;
    }

    private function registerTraceableUpdateOrchestrator(ContainerBuilder $container): void
    {
        $container->register(TraceableUpdateOrchestrator::class, TraceableUpdateOrchestrator::class)
            ->setArguments([
                new Reference(UpdateOrchestratorInterface::class.self::INNER, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->setDecoratedService(UpdateOrchestratorInterface::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
        ;
    }

    private function registerDataCollector(ContainerBuilder $container): void
    {
        $container->register(MeiliSearchBundleDataCollector::class, MeiliSearchBundleDataCollector::class)
            ->setArguments([
                new Reference(TraceableIndexOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(TraceableDocumentEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(TraceableSearchEntryPoint::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference(TraceableSynonymsOrchestrator::class, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->addTag('data_collector', [
                'template' => 'data_collector.html.twig',
                'id'       => 'app.meili_search_collector',
            ])
        ;
    }
}
