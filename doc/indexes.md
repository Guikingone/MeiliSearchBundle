# Indexes

Indexes are the main entities in MeiliSearch, 
as explained in the [official documentation](https://docs.meilisearch.com/guides/main_concepts/indexes.html#index-creation).

This bundle defines index in the configuration:

```yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    indexes:
        admin_posts:
            primaryKey: 'id'

# ...
```

## Configuration

Each indexes can define a set of configurations keys that defines synonyms, attributes, etc, here's an example:

```yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    indexes:
        foo:
            primaryKey: 'id'
            async: true
            distinctAttribute: 'header'
            facetedAttributes: ['title', 'creation_date']
            searchableAttributes: ['id', 'title', 'header', 'tags', 'creation_date']
            displayedAttributes: ['id', 'title', 'header', 'tags', 'url']
            synonyms: 
                wolverine: ['xmen', 'logan', 'jackman']
```

Let's get into the details of each key:

- **primaryKey**: The primary key of the index, by default, MeiliSearch try to [infers it](https://docs.meilisearch.com/guides/main_concepts/documents.html#primary-field).
- **async**: Define if actions related to this index must be performed asynchronously (requires `symfony/messenger`).
- **distinctAttribute**: Set the field of documents where the value will always be [unique](https://docs.meilisearch.com/guides/advanced_guides/distinct.html).
- **facetedAttributes**: 
- **searchableAttributes**: 
- **displayedAttributes**: 
- **synonyms**: 

## Prefix

Every index can be prefixed via a "high-level" key, this approach can be useful for specific environment usage. 

```yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    prefix: '_env'
    indexes:
        foo:
            primaryKey: 'id'
            async: true
            distinctAttribute: 'header'
            facetedAttributes: ['title', 'creation_date']
            searchableAttributes: ['id', 'title', 'header', 'tags', 'creation_date']
            displayedAttributes: ['id', 'title', 'header', 'tags', 'url']
            synonyms: 
                wolverine: ['xmen', 'logan', 'jackman']
```

Once set, the indexes need to be updated via `meili:update-indexes`.

Once the update's done, the prefix's used when you trigger a search via [SearchEntryPoint](../src/Search/SearchEntryPoint.php)
is automatically set using this configuration value.

**PS: Keep in mind that you need to update your document loader's to use the new prefixed indexes.**

## Storage

By default, every index's stored in [IndexMetadataRegistry](../src/Metadata/IndexMetadataRegistry.php) then dumped 
into the directory configured via the key `metadata_directory`, more info on [configuration](configuration.md).

**Important: The storage only store metadata about indexes, the documents cannot be stored without MeiliSearch**

### Extending indexes storage

As explained before, by default, this bundle stores the metadata of every index in the directory configured via `metadata_directory`,
sometimes, you may need to store it outside of the application (think redundant environment, Cloud, etc). 

In order to achieve this, the [IndexMetadataRegistryInterface](../src/Metadata/IndexMetadataRegistryInterface.php) is available:

```php
<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

use Countable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexMetadataRegistryInterface extends Countable
{
    public function add(string $index, IndexMetadataInterface $metadata): void;

    public function override(string $index, IndexMetadataInterface $newConfiguration): void;

    public function get(string $index): IndexMetadataInterface;

    public function remove(string $index): void;

    public function has(string $index): bool;

    public function clear(): void;

    /**
     * @return array<string, IndexMetadataInterface>
     */
    public function toArray(): array;
}
```
