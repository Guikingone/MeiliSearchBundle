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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('meili_search');

        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('host')
                        ->defaultValue('http://127.0.0.1')
                    ->end()
                    ->scalarNode('apiKey')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('cache')
                        ->children()
                            ->scalarNode('enabled')
                                ->info('Enable the CachedSearchEntryPoint')
                                ->defaultValue(false)
                            ->end()
                            ->scalarNode('update_on_new_document')
                                ->info('Update the cache every time that a document is added')
                                ->defaultValue(true)
                            ->end()
                            ->scalarNode('pool')
                                ->info('The cache pool used by CachedSearchEntryPoint, default to "app"')
                                ->defaultValue('app')
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('prefix')
                        ->info('Define a prefix for each indexes')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('indexes')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('primaryKey')
                                    ->info('https://docs.meilisearch.com/guides/main_concepts/indexes.html#primary-key')
                                    ->defaultNull()
                                ->end()
                                ->scalarNode('async')
                                    ->info('Define if every document actions must be performed via a queue')
                                    ->defaultValue(false)
                                ->end()
                                ->arrayNode('rankingRules')
                                    ->info('https://docs.meilisearch.com/guides/main_concepts/relevancy.html#ranking-rules')
                                    ->scalarPrototype()->end()
                                    ->defaultValue(['typo', 'words', 'proximity', 'attribute', 'wordsPosition', 'exactness'])
                                ->end()
                                ->arrayNode('stopWords')
                                    ->info('https://docs.meilisearch.com/guides/main_concepts/indexes.html#stop-words')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                                ->scalarNode('distinctAttribute')
                                    ->info('https://docs.meilisearch.com/guides/advanced_guides/distinct.html')
                                    ->defaultNull()
                                ->end()
                                ->arrayNode('facetedAttributes')
                                    ->info('https://docs.meilisearch.com/guides/advanced_guides/faceted_search.html')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                                ->arrayNode('searchableAttributes')
                                    ->info('https://docs.meilisearch.com/guides/advanced_guides/field_properties.html#searchable-fields')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                                ->arrayNode('displayedAttributes')
                                    ->info('https://docs.meilisearch.com/guides/advanced_guides/field_properties.html#displayed-fields')
                                    ->scalarPrototype()->end()
                                    ->defaultValue([])
                                ->end()
                                ->arrayNode('synonyms')
                                    ->info('https://docs.meilisearch.com/guides/advanced_guides/synonyms.html')
                                    ->useAttributeAsKey('name')
                                    ->arrayPrototype()
                                        ->children()
                                            ->arrayNode('values')
                                                ->scalarPrototype()->end()
                                                ->defaultValue([])
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
