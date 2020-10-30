# Cache

This bundle provides an integration with [the Cache component](https://symfony.com/doc/current/components/cache.html), 
every search/result can be stored in cached for better performances and reliability 
(think of offline access, fallback, etc). 

To enable it, just use the `cache` directive in the configuration:

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    metadata_directory: '%kernel.root_dir%/var/_ms'
    cache:
      enabled: true
```

## Overriding the cache pool

By default, the bundle will use the `cache.app` pool, if you need to overwrite it: 

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    metadata_directory: '%kernel.root_dir%/var/_ms'
    cache:
      enabled: true
      pool: 'custom.pool'
```

## Results update

As the cache store the end result of a search, you may have sometimes differences between
what the cache contains and what MeiliSearch hold, to get rid of this issue: 

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    metadata_directory: '%kernel.root_dir%/var/_ms'
    cache:
      enabled: true
      clear_on_new_document: true
      clear_on_document_update: true
```

The two keys are equals to `false` by default, once set to `true`, every action on creation/update
invalidate the cache, as results can evolve, the recalculation's done in the next search. 

## Fallback

You may need to use the cache as a fallback 
when the [SearchEntryPoint](../src/Search/SearchEntryPoint.php) fails (or if it comes to fail),
this bundle allows you to configure this behaviour via the configuration:

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    metadata_directory: '%kernel.root_dir%/var/_ms'
    cache:
      enabled: true
      fallback: true
```

**Note:** Keep in mind that this use case can require the previous configuration options
to return up-to-date results (depending on your use cases).
