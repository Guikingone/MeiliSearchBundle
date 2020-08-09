<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Document\TraceableDocumentEntryPoint;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\IndexSettingsOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Index\TraceableIndexOrchestrator;
use MeiliSearchBundle\Index\TraceableIndexSettingsOrchestrator;
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
    private const DEBUG = '.debug.';
    private const INNER = '.inner';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->registerTraceableIndexOrchestrator($container);
        $this->registerTraceableIndexSettingsOrchestrator($container);
        $this->registerTraceableDocumentOrchestrator($container);
        $this->registerTraceableSearchEntryPoint($container);
        $this->registerTraceableSynonymsOrchestrator($container);
        $this->registerTraceableUpdateOrchestrator($container);
        $this->registerDataCollector($container);
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
            ->addTag('kernel.reset', [
                'method' => 'reset',
            ])
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
                'priority' => 320,
            ])
        ;
    }
}
