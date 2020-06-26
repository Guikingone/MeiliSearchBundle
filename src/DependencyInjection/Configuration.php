<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('meili_search');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('host')->end()
                    ->scalarNode('api_key')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
