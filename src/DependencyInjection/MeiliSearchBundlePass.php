<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $this->registerTraceableDataCollector($container);
    }

    private function registerTraceableDataCollector(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('meili_search.data_collector.traceable')) as $id) {
            $container->getDefinition($id)->addTag('kernel.reset', [
                'method' => 'reset',
            ]);
        }
    }
}
