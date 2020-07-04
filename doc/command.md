# Commands

This bundle defines a set of command used to interact with the MeiliSearch API. 

## List indexes

```bash
bin/console meili:list-indexes test
```

List every index stored in MeiliSearch

## Delete indexes

```bash
bin/console meili:delete-index foo
```

Allow to delete an index.

## Load documents

```bash
bin/console meili:warm test
```

This command allow to load documents into a specific index thanks to `DataProviders`.

## Clearing the search result cache

```bash
bin/console meili:clear-search-cache
```

This command allow to clear the search result cache.
