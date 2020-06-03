<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\DocumentOrchestrator;
use MeiliSearchBundle\Client\IndexOrchestrator;
use MeiliSearchBundle\Client\SearchEntryPoint;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $clientDefinition = (new Definition(Client::class))
            ->setArguments([
                $config['host'],
                $config['api_key'] ?? null,
                new Reference('http_client', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
        ;
        $container->setDefinition('meili_search.client', $clientDefinition);

        $documentOrchestratorDefinition = (new Definition(DocumentOrchestrator::class))
            ->setArguments([
                new Reference('meili_search.client'),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.document_orchestrator')
        ;
        $container->setDefinition('meili_search.document_orchestrator', $documentOrchestratorDefinition);

        $indexOrchestratorDefinition = (new Definition(IndexOrchestrator::class))
            ->setArguments([
                new Reference('meili_search.client'),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.index_orchestrator')
        ;
        $container->setDefinition('meili_search.index_orchestrator', $indexOrchestratorDefinition);

        $searchEntryPoint = (new Definition(SearchEntryPoint::class))
            ->setArguments([
                new Reference('meili_search.index_orchestrator'),
                new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
        ;
        $container->setDefinition('meili_search.entry_point', $searchEntryPoint);
    }
}
