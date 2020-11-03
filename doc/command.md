# Commands

This bundle defines a set of command used to interact with the MeiliSearch API.

## Warm indexes

```bash
bin/console meili:warm-indexes
```

Warm indexes in MeiliSearch.

**PS: This command should only be used when creating indexes**

## Update indexes

```bash
bin/console meili:update-indexes
```

Update existing indexes in MeiliSearch.

**PS: The `force` option can be used**

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
bin/console meili:warm-documents
```

This command allow loading documents via [DataProviders](data_provider.md).

## Clearing the search result cache

```bash
bin/console meili:clear-search-cache
```

This command allow to clear the search result cache (if the [cache](cache.md) on search's enabled).
