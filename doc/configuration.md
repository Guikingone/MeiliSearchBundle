# Configuration

This bundle allows you to connect and configure the indexes of a MeiliSearch instance:

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    metadata_directory: '%kernel.root_dir%/var/_ms'
    prefix: '_app'
    indexes:
        admin_posts:
            primaryKey: 'id'
            async: true
            distinctAttribute: 'header'
            facetedAttributes: ['title', 'creation_date']
            searchableAttributes: ['id', 'title', 'header', 'tags', 'creation_date']
            displayedAttributes: ['id', 'title', 'header', 'tags', 'url']
            synonyms: 
                wolverine: ['xmen', 'logan', 'jackman']
    scoped_indexes:
        admin: ['admin_posts', 'tags']
```

## General configuration

Here's a full breakdown of each configuration keys:

- **host**: The address of the MeiliSearch instance.

- **apiKey**: The key used to connect to the MeiliSearch instance, more info on the [official documentation]('https://docs.meilisearch.com/guides/advanced_guides/authentication.html#master-key').

- **metadata_directory**: Used to store the metadata (mostly indexes) locally, this directory must be writable (or symlinked).

- **prefix**: Used to define a prefix for each index.

- **indexes**: A list of indexes to create along with their configuration.

- **scoped_indexes**: A list of indexes groups that can be used to search.

## Indexes

Each index allows to configure the following keys:

- **[primaryKey](https://docs.meilisearch.com/guides/main_concepts/documents.html#primary-key)**: The attribute used to identify each document related to an index.

- **async**: Define if every document actions must be performed using a queue (requires `Symfony/Messenger`).

- **[rankingRules](https://docs.meilisearch.com/guides/main_concepts/relevancy.html#ranking-rules)**: An array of  

- **[stopWords](https://docs.meilisearch.com/guides/advanced_guides/stop_words.html#language-driven)**: An array of words ignored in search queries 

- **[distinctAttribute](https://docs.meilisearch.com/guides/advanced_guides/distinct.html)**: Define the field that will be returned only once if multiple results with the same value are found.

- **[facetedAttributes](https://docs.meilisearch.com/guides/advanced_guides/faceted_search.html)**: An array of attributes used for faceted search

- **[searchableAttributes](https://docs.meilisearch.com/guides/advanced_guides/field_properties.html#searchable-fields)**: An array of fields that can be used to trigger a search.

- **[displayedAttributes](https://docs.meilisearch.com/guides/advanced_guides/field_properties.html#displayed-fields)**: An array of fields that are displayed for each matching documents.

- **[synonyms](https://docs.meilisearch.com/guides/advanced_guides/synonyms.html)**: A list of synonyms to create for this index
