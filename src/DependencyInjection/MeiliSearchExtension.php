<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\DocumentOrchestrator;
use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\Client\IndexOrchestrator;
use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Client\InstanceProbe;
use MeiliSearchBundle\Client\InstanceProbeInterface;
use MeiliSearchBundle\src\EventSubscriber\ExceptionSubscriber;
use MeiliSearchBundle\src\Update\UpdateOrchestratorInterface;
use MeiliSearchBundle\Update\UpdateOrchestrator;
use MeiliSearchBundle\Search\SearchEntryPoint;
use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
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
            ->addTag('meili_search.client')
        ;
        $container->setDefinition('meili_search.client', $clientDefinition);

        $indexOrchestratorDefinition = (new Definition(IndexOrchestrator::class))
            ->setArguments([
                new Reference('meili_search.client'),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.index_orchestrator')
        ;
        $container->setDefinition('meili_search.index_orchestrator', $indexOrchestratorDefinition);
        $container->setAlias(IndexOrchestratorInterface::class, 'meili_search.index_orchestrator');

        $documentOrchestratorDefinition = (new Definition(DocumentOrchestrator::class))
            ->setArguments([
                new Reference('meili_search.client'),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.document_orchestrator')
        ;
        $container->setDefinition('meili_search.document_orchestrator', $documentOrchestratorDefinition);
        $container->setAlias(DocumentOrchestratorInterface::class, 'meili_search.document_orchestrator');

        $instanceProbeDefinition = (new Definition(InstanceProbe::class))
            ->setArguments([
                new Reference('meili_search.client'),
            ])
            ->addTag('meili_search.instance_probe')
        ;
        $container->setDefinition('meili_search.instance_probe', $instanceProbeDefinition);
        $container->setAlias(InstanceProbeInterface::class, 'meili_search.instance_probe');

        $searchEntryPoint = (new Definition(SearchEntryPoint::class))
            ->setArguments([
                new Reference('meili_search.index_orchestrator'),
                new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.entry_point')
        ;
        $container->setDefinition('meili_search.entry_point', $searchEntryPoint);
        $container->setAlias(SearchEntryPointInterface::class, 'meili_search.entry_point');

        $updateOrchestratorDefinition = (new Definition(UpdateOrchestrator::class))
            ->setArguments([
                new Reference('meili_search.client'),
            ])
            ->addTag('meili_search.update_orchestrator')
        ;
        $container->setDefinition('meili_search.update_orchestrator', $updateOrchestratorDefinition);
        $container->setAlias(UpdateOrchestratorInterface::class, 'meili_search.update_orchestrator');

        $exceptionSubscriberDefinition = (new Definition(ExceptionSubscriber::class))
            ->setArguments([
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('kernel.event_subscriber')
        ;
        $container->setDefinition('meili_search.exception_subscriber', $exceptionSubscriberDefinition);

        $container->registerForAutoconfiguration(DocumentDataProviderInterface::class)->setTags(['meili_search_bundle.document_provider']);
    }
}
