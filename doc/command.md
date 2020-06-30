# Commands

This bundle defines a set of command used to interact with the MeiliSearch API. 

## Create indexes

```bash
bin/console meili:create-indexes foo --primary_key bar
```

This command allows to create a new index and allows you to define the primary key used by this index.

### Success output:

### Error output:

## List indexes

```bash
bin/console meili:list-indexes test
```

List every index stored in MeiliSearch

### Success output:

### Error output:

## Delete indexes

```bash
bin/console meili:delete-index foo
```

Allow to delete a valid index

### Success output:

### Error output:

## Load documents

```bash
bin/console meili:warm test
```

This command allow to load documents into a specific index thanks to `DataProviders`

### Success output:

### Error output:
