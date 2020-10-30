# Commands

This bundle defines a set of command used to interact with the MeiliSearch API.

## Warm indexes

```bash
bin/console meili:warm-indexes
```

Warm indexes in MeiliSearch (existing indexes are updated).

## List indexes

```bash
bin/console meili:list-indexes
```

List every index stored in MeiliSearch

## Delete index

```bash
bin/console meili:delete-index foo
```

Allow to delete an index.

## Delete indexes

```bash
bin/console meili:delete-indexes
```

Allow to delete all the indexes.

## Load documents

```bash
bin/console meili:warm test
```

This command allow loading documents into a specific index thanks to [DataProviders](data_provider.md).

## Clearing the search result cache

```bash
bin/console meili:clear-search-cache
```

This command allow to clear the search result cache (if the [cache](cache.md) on search is enabled).
