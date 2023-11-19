<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\DependencyInjection;

use Meilisearch\Client;
use MeiliSearchBundle\DependencyInjection\MeiliSearchBundlePass;
use MeiliSearchBundle\Index\IndexOrchestrator;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Index\SynonymsOrchestrator;
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

        static::assertTrue($container->getDefinition(SynonymsOrchestrator::class)->hasTag('kernel.reset'));
        static::assertSame('reset', $container->getDefinition(SynonymsOrchestrator::class)->getTag('kernel.reset')[0]['method']);
    }

    private function getContainer(): ContainerBuilder
    {
        $client = $this->createMock(Client::class);

        $container = new ContainerBuilder();
        $container->setDefinition(
            IndexOrchestrator::class,
            (new Definition(IndexOrchestrator::class, [
                $client,
            ]))
        );
        $container->setAlias(IndexOrchestratorInterface::class, IndexOrchestrator::class);

        $container->register(SynonymsOrchestrator::class, SynonymsOrchestrator::class)
            ->setArguments([
                new Reference(IndexOrchestratorInterface::class),
            ])
            ->addTag('meili_search.data_collector.traceable')
        ;

        return $container;
    }
}
