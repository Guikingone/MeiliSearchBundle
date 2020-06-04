<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearchBundle\Client\TraceableDocumentOrchestrator;
use MeiliSearchBundle\Client\TraceableIndexOrchestrator;
use MeiliSearchBundle\Client\TraceableSearchEntryPoint;
use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundlePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->registerTraceableIndexOrchestrator($container);
        $this->registerTraceableDocumentOrchestrator($container);
        $this->registerTraceableSearchEntryPoint($container);
        $this->registerDataCollector($container);
    }

    private function registerTraceableIndexOrchestrator(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'debug.meili_search.index_orchestrator',
            (new Definition(TraceableIndexOrchestrator::class, [
                new Reference('meili_search.index_orchestrator'),
            ]))->setDecoratedService('meili_search.index_orchestrator')
        );
    }

    private function registerTraceableDocumentOrchestrator(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'debug.meili_search.document_orchestrator',
            (new Definition(TraceableDocumentOrchestrator::class, [
                new Reference('meili_search.document_orchestrator'),
            ]))->setDecoratedService('meili_search.document_orchestrator')
        );
    }

    private function registerTraceableSearchEntryPoint(ContainerBuilder $container): void
    {
        $container->setDefinition(
            'debug.meili_search.entry_point',
            (new Definition(TraceableSearchEntryPoint::class, [
                new Reference('meili_search.entry_point'),
            ]))->setDecoratedService('meili_search.entry_point')
        );
    }

    private function registerDataCollector(ContainerBuilder $container): void
    {
        $dataCollector = (new Definition(MeiliSearchBundleDataCollector::class))
            ->setArguments([
                new Reference('debug.meili_search.index_orchestrator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference('debug.meili_search.document_orchestrator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                new Reference('debug.meili_search.entry_point', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->addTag('data_collector', [
                'template' => 'data_collector.html.twig',
                'id'       => 'app.request_collector',
            ])
        ;

        $container->setDefinition('meili_search.data_collector', $dataCollector);
    }
}
