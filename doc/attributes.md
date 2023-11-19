# Attributes

This bundle allows you to define attributes to ease the configuration:

- [Document](../src/Bridge/Doctrine/Attribute/Document.php) define a document related to an `index`

## Defining a document

Defining a document is as simple as it sounds:

```php
<?php

use MeiliSearchBundle\Bridge\Doctrine\Attribute as MeiliSearch;

#[MeiliSearch\Document(index: 'bar', primaryKey: 'id')]
final class Foo
{
    // ...
}
```

Once defined, every time that you persist, update or remove an instance of `Foo`,
a subscriber will handle the related operations.

## Using a model

When submitting a document into MeiliSearch, you may need to retrieve an object
when searching, if you're not using an "entity", the way-to-do is defined [here](dto.md).

When using an entity, the `Document` attribute define a third argument `model`
that allows you to specify that this class must be used to building an object
after a successful search, let's see how to use it:

```php
<?php

use MeiliSearchBundle\Bridge\Doctrine\Attribute as MeiliSearch;
// ...

#[MeiliSearch\Document(index: 'bar', primaryKey: 'id', model: true)]
final class Foo
{
    // ...
}
```

Once persisted, the "document payload" stored in MeiliSearch will contain a `model` key
which contain the FQCN of the current object, when a search occurs,
the [ResultBuilder](../src/Result/ResultBuilder.php) will use this value to tell
the Serializer which class need to be built.
