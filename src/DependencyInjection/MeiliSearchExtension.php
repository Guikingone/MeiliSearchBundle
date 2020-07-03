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
use MeiliSearchBundle\Command\CreateIndexCommand;
use MeiliSearchBundle\Command\DeleteIndexCommand;
use MeiliSearchBundle\Command\ListIndexesCommand;
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
    private const CLIENT_IDENTIFIER = 'meili_search.client';
    private const INDEX_ORCHESTRATOR_IDENTIFIER = 'meili_search.index_orchestrator';
    private const DOCUMENT_ORCHESTRATOR_IDENTIFIER = 'meili_search.document_orchestrator';

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
            ->addTag(self::CLIENT_IDENTIFIER)
        ;
        $container->setDefinition(self::CLIENT_IDENTIFIER, $clientDefinition);

        $indexOrchestratorDefinition = (new Definition(IndexOrchestrator::class))
            ->setArguments([
                new Reference(self::CLIENT_IDENTIFIER),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag(self::INDEX_ORCHESTRATOR_IDENTIFIER)
        ;
        $container->setDefinition(self::INDEX_ORCHESTRATOR_IDENTIFIER, $indexOrchestratorDefinition);
        $container->setAlias(IndexOrchestratorInterface::class, self::INDEX_ORCHESTRATOR_IDENTIFIER);

        $documentOrchestratorDefinition = (new Definition(DocumentOrchestrator::class))
            ->setArguments([
                new Reference(self::CLIENT_IDENTIFIER),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag(self::DOCUMENT_ORCHESTRATOR_IDENTIFIER)
        ;
        $container->setDefinition(self::DOCUMENT_ORCHESTRATOR_IDENTIFIER, $documentOrchestratorDefinition);
        $container->setAlias(DocumentOrchestratorInterface::class, self::DOCUMENT_ORCHESTRATOR_IDENTIFIER);

        $instanceProbeDefinition = (new Definition(InstanceProbe::class))
            ->setArguments([
                new Reference(self::CLIENT_IDENTIFIER),
            ])
            ->addTag('meili_search.instance_probe')
        ;
        $container->setDefinition('meili_search.instance_probe', $instanceProbeDefinition);
        $container->setAlias(InstanceProbeInterface::class, 'meili_search.instance_probe');

        $searchEntryPoint = (new Definition(SearchEntryPoint::class))
            ->setArguments([
                new Reference(self::INDEX_ORCHESTRATOR_IDENTIFIER),
                new Reference('event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->addTag('meili_search.entry_point')
        ;
        $container->setDefinition('meili_search.entry_point', $searchEntryPoint);
        $container->setAlias(SearchEntryPointInterface::class, 'meili_search.entry_point');

        $updateOrchestratorDefinition = (new Definition(UpdateOrchestrator::class))
            ->setArguments([
                new Reference(self::CLIENT_IDENTIFIER),
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

        $this->registerCommands($container);
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $createIndexCommandDefinition = (new Definition(CreateIndexCommand::class))
            ->setArguments([
                new Reference(self::INDEX_ORCHESTRATOR_IDENTIFIER),
            ])
            ->addTag('console.command')
        ;
        $deleteIndexCommandDefinition = (new Definition(DeleteIndexCommand::class))
            ->setArguments([
                new Reference(self::INDEX_ORCHESTRATOR_IDENTIFIER),
            ])
            ->addTag('console.command')
        ;
        $listIndexesCommandDefinition = (new Definition(ListIndexesCommand::class))
            ->setArguments([
                new Reference(self::INDEX_ORCHESTRATOR_IDENTIFIER),
            ])
            ->addTag('console.command')
        ;

        $container->setDefinition('meili_search.create_index_command', $createIndexCommandDefinition);
        $container->setDefinition('meili_search.delete_index_command', $deleteIndexCommandDefinition);
        $container->setDefinition('meili_search.list_indexes_command', $listIndexesCommandDefinition);
    }
}
