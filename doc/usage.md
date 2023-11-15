# Usage

Once the installation process done using [the README instructions](./../README.md), let's start a first search process:

## Create an index

Indexes are defined in the `meili_search.yaml` file:

```yaml
# meili_search.yaml
meili_search:
    host: 'http://127.0.0.1'
    apiKey: '%env(MEILI_SEARCH_API_KEY)%'
    indexes:
        posts:
            primaryKey: 'title'
            # ...
```

Once defined, the indexes need to be sent to MeiliSearch:

```bash
php bin/console meili:warm-indexes
```

_Note: You can find more information about the configurations keys in [the configuration section](configuration.md)._

## Load documents

This bundle provides two approaches when it comes to loading documents:

- Using `DataProvider's` which are responsible for fetching data from "external" source and returning an array
- Using the `Document` attribute on Doctrine entities and letting the bundle handle the "CRUD" aspect.

### Without Doctrine

In order to load documents, this bundle provides a [DocumentDataProviderInterface](../src/DataProvider/DocumentDataProviderInterface.php):

```php
<?php

namespace App\DataProvider;

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;

final class FooProvider implements DocumentDataProviderInterface
{
    public function support() : string
    {
        return 'posts';
    }

    public function getDocument() : array
    {
        $data = ... // Could be a repository|external API call followed by a transformation into an array

        return $data;
    }

}
```

Once the provider defined, a command allows you to load the data into the MeiliSearch API:

```bash
php bin/console meili:warm-documents
```

### With Doctrine

In order to load documents from entities, this bundle provides a [Document](../src/Bridge/Doctrine/Attribute/Document.php) attribute:

```php
<?php

namespace App\Entity;

use MeiliSearchBundle\Bridge\Doctrine\Attribute as MeiliSearch;
// ...

/**
 * @MeiliSearch\Document(index="posts")
 */
class Post
{
    // ...
}
```

This configuration is enough for submitting the data into MeiliSearch,
the important part is the index name which allows to link this document 
to the index when persisting it.
When an object of type `Post` is persisted, updated or removed, 
the related actions are performed into the MeiliSearch API.

## Search

In order to ease the search process, 
this bundle defines a [SearchEntryPoint](../src/Search/SearchEntryPoint.php) that allow you 
to build a search, let's say that we've persisted a post with the title `Random`:

```php
<?php

use MeiliSearchBundle\Search\SearchEntryPointInterface;

// ...

final class FooController
{
    public function index(SearchEntryPointInterface $searchEntryPoint)
    {
        $data = $searchEntryPoint->search('posts', 'Random');
        
        // Do something with the search result
    }
}
```

If the search is valid, you must receive a [Search](../src/Search/SearchResult.php) instance in `$data`,
this last one contain a set of methods and shortcuts that allows you to play with the search result.
By default, the 'hits' are an associative array, we'll see later how to retrieve objects.
