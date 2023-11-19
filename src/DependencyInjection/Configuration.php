<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use function interface_exists;

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

        /* @phpstan-ignore-next-line */
        $treeBuilder
            ->getRootNode()
                ->children()
                    ->scalarNode('host')
                        ->defaultValue('http://127.0.0.1')
                    ->end()
                    ->scalarNode('apiKey')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('metadata_directory')
                        ->info('Define the directory filters are stored the metadata')
                        ->defaultValue('%kernel.project_dir%/var/_ms')
                    ->end()
                    ->arrayNode('cache')
                        ->validate()
                            ->always()
                            ->then(static function (array $cacheConfiguration): array {
                                switch ($cacheConfiguration) {
                                    case $cacheConfiguration['enabled'] && !interface_exists(AdapterInterface::class):
                                        throw new InvalidConfigurationException('The cache cannot be enabled without the "symfony/cache" package');
                                    case !$cacheConfiguration['enabled'] && $cacheConfiguration['clear_on_new_document']:
                                        throw new InvalidConfigurationException('The cache must be enabled to use the "clear_on_new_document" option');
                                    case !$cacheConfiguration['enabled'] && $cacheConfiguration['clear_on_document_update']:
                                        throw new InvalidConfigurationException('The cache must be enabled to use the "clear_on_document_update" option');
                                    case !$cacheConfiguration['enabled'] && $cacheConfiguration['fallback']:
                                        throw new InvalidConfigurationException('The cache must be enabled to use the "fallback" option');
                                    default:
                                        return $cacheConfiguration;
                                }
                            })
                        ->end()
                        ->children()
                            ->scalarNode('enabled')
                                ->info('Enable the CachedSearchEntryPoint')
                                ->defaultValue(false)
                            ->end()
                            ->scalarNode('clear_on_new_document')
                                ->info('Clear the cache every time that a document is added')
                                ->defaultValue(false)
                            ->end()
                            ->scalarNode('clear_on_document_update')
                                ->info('Clear the cache every time that a document is updated')
                                ->defaultValue(false)
                            ->end()
                            ->scalarNode('fallback')
                                ->info('Define if the cache is used as a fallback for every search')
                                ->defaultValue(false)
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
                                    ->variablePrototype()->end()
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
