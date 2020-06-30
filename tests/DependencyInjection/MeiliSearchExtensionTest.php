<?php

declare(strict_types=1);

namespace DependencyInjection;

use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Client\InstanceProbeInterface;
use MeiliSearchBundle\DependencyInjection\MeiliSearchExtension;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\src\Update\UpdateOrchestratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchExtensionTest extends TestCase
{
    public function testDefinitionsAreRegistered(): void
    {
        $extension = new MeiliSearchExtension();

        $container = new ContainerBuilder();
        $extension->load([], $container);

        static::assertTrue($container->hasDefinition('meili_search.client'));
        static::assertTrue($container->hasDefinition('meili_search.index_orchestrator'));
        static::assertTrue($container->hasAlias(IndexOrchestratorInterface::class));
        static::assertTrue($container->hasDefinition('meili_search.document_orchestrator'));
        static::assertTrue($container->hasAlias(DocumentOrchestratorInterface::class));
        static::assertTrue($container->hasDefinition('meili_search.instance_probe'));
        static::assertTrue($container->hasAlias(InstanceProbeInterface::class));
        static::assertTrue($container->hasDefinition('meili_search.entry_point'));
        static::assertTrue($container->hasAlias(SearchEntryPointInterface::class));
        static::assertTrue($container->hasDefinition('meili_search.update_orchestrator'));
        static::assertTrue($container->hasAlias(UpdateOrchestratorInterface::class));
    }
}
