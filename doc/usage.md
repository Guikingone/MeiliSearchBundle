# Usage

Once the installation process done using [the README instructions](./../README.md), let's start a first search process:

## Create an index

MeiliSearch use indexes to store documents, time to create a simple index!

```bash
bin/console meili:create-index foo

[OK] The "foo" index has been created
```

Once the index created, let's verify that everything goes right:

```bash
bin/console meili:list-indexes

// The following indexes have been found:

```

// TODO: screen

The table must display the newly created index. 

## Load documents

Time to load some documents on the newly created index, first, define a [DataProvider](data_provider.md).
Once defined, time to load everything in MeiliSearch:

```bash
bin/console meili:warm foo

// Currently loading the documents for the "foo" index

[OK] The document have been imported, feel free to search them!

```

Once loaded (could take some time depending on the available resources), time to test a fresh search!

## Search

In order to ease the search process, this bundle defines a `SearchEntryPoint` that allow you to build a search:

```php
<?php

use MeiliSearchBundle\Search\SearchEntryPointInterface;

// ...

final class FooController
{
    public function index(SearchEntryPointInterface $searchEntryPoint)
    {
        $data = $searchEntryPoint->search('foo', 'bar');
        
        // Do something with the search result
    }
}
```

If the search is valid, you must receive a `Search` instance in `$data`,
this last one contain a set of methods and shortcuts that allows you to play with the search result.
