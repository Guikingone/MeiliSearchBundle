<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use MeiliSearch\Client;
use MeiliSearchBundle\Client\DocumentOrchestrator;
use MeiliSearchBundle\Client\IndexOrchestrator;
use MeiliSearchBundle\Client\SearchEntryPoint;
use MeiliSearchBundle\DependencyInjection\MeiliSearchBundlePass;
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

        static::assertTrue($container->hasDefinition('debug.meili_search.index_orchestrator'));
        static::assertTrue($container->hasDefinition('debug.meili_search.document_orchestrator'));
        static::assertTrue($container->hasDefinition('debug.meili_search.entry_point'));
        static::assertTrue($container->hasDefinition('meili_search.data_collector'));
    }

    private function getContainer(): ContainerBuilder
    {
        $client = $this->createMock(Client::class);

        $container = new ContainerBuilder();
        $container->setDefinition('meili_search.index_orchestrator', (new Definition(IndexOrchestrator::class, [
            $client,
        ])));
        $container->setDefinition('meili_search.document_orchestrator', (new Definition(DocumentOrchestrator::class, [
            $client,
        ])));
        $container->setDefinition('meili_search.entry_point', (new Definition(SearchEntryPoint::class, [
            new Reference('meili_search.index_orchestrator'),
        ])));

        return new ContainerBuilder();
    }
}
