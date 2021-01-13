# Migrations (new in **0.2**)

This bundle provides an [IndexSynchronizer](../src/Index/IndexSynchronizer.php) which allows keeping
track of indexes synchronization between the MeiliSearch instance and the metadata registry.

A [DocumentMigrationOrchestrator](../src/Document/DocumentMigrationOrchestrator.php) is also provided
to help migrate documents between indexes.

## Migrating documents

Migrating documents between indexes can be hard, 
thanks to [DocumentMigrationOrchestrator](../src/Document/DocumentMigrationOrchestrator.php), 
this process can be eased, here's the process to follow: 

Let's start with 2 indexes:

- `foo`
- `bar`

Imagine that you want to migrate documents from `foo` to `bar` (and remove every document in `foo`),
the migration is done via a single command:

```bash
$ bin/console meili:migrate-documents foo --index bar --remove
```

Let's dive into the different arguments:

- `foo` represent the "old index" that you want to migrate documents from.
- `--index bar` represent the index where documents will be migrated.
- `--remove` define the action to remove the migrated documents once the migration is done.

If everything goes fine, the following output will be displayed:

```bash
[SUCCESS] The documents have been migrated
```

**It's important to notice is that before migrating documents, a dump is created**

_Note: Keep in mind that shortcuts are defined for `index` (aka `i`)  and `remove` (aka `r`)._
