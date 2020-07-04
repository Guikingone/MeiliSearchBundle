# Data provider

Data providers are the main entry point for every document that you may want to add in MeiliSearch.
Data providers aims to allow you to submit data when you cannot use annotations.

The main role of a data provider is to return an array that contain the data stored as a document in MeiliSearch.

## Defining a data provider

In order to see how to use it, let's use a data provider which receive data from an external REST API:

_In this example, we use `Symfony/HttpClient` but feel free to use the one which fit your needs_

```php
<?php

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
// ...

final class PostDataProvider implements DocumentDataProviderInterface
{
    private $httpClient;

    // ... constructor

    public function support() : string
    {
        return 'foo';
    }

    public function getDocument() : array
    {
        $response = $this->httpClient->request('GET', 'https://api.com/posts');
        if (0 === \count($response->toArray())) {
            return [];
        }
        
        // TODO
    }
}
``` 

The idea here is pretty simple, every post is a sub-array of `$collection`, this way,
when you submit the `getDocument()` return value in MS, you link every post to the `foo` index.

Keep in mind that a provider can return data from any source, as long as the array is valid, you're good to go.

Once defined, the data provider is automatically configured and injected both in the DIC and the related command.

## Overriding primary keys

As you may need to use a different primary key from the default `id` one, this bundle defines a `PrimaryKeyOverrideDataProviderInterface`
which allows you to define the key to use: 

```php
<?php

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\PrimaryKeyOverrideDataProviderInterface;
// ...

final class PostDataProvider implements DocumentDataProviderInterface, PrimaryKeyOverrideDataProviderInterface
{
    private $postRepository;

    // ... constructor

    public function support() : string
    {
        return 'foo';
    }

    public function getPrimaryKey() : string
    {
        return 'key';
    }

    public function getDocument() : array
    {
        $posts = $this->postRepository->findAll();
        if (0 === \count($posts)) {
            return [];
        }

        $collection = [];
        array_walk($posts, function (Post $post) use (&$collection): void {
            $collection[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'tags' => $post->getTagsAsArray(),
            ];
        });

        return $collection;
    }
}
```

## Setting a model (related to [DTO/Value Object](dto.md))

When loading a document, you may need to define a model used to obtain an objet when fetching the document.
This bundle provides a `ModelDataProviderInterface` which allows you to set the model thanks to `getModel()`: 

```php
<?php

use MeiliSearchBundle\DataProvider\DocumentDataProviderInterface;
use MeiliSearchBundle\DataProvider\ModelDataProviderInterface;
// ...

final class PostDataProvider implements DocumentDataProviderInterface, ModelDataProviderInterface
{
    private $postRepository;

    // ... constructor

    public function support(): string
    {
        return 'foo';
    }

    public function getModel(): string
    {
        return Foo::class;
    }

    public function getDocument(): array
    {
        $posts = $this->postRepository->findAll();
        if (0 === \count($posts)) {
            return [];
        }

        $collection = [];
        array_walk($posts, function (Post $post) use (&$collection): void {
            $collection[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'tags' => $post->getTagsAsArray(),
            ];
        });

        return $collection;
    }
}
```

## Using a priority

Sometimes, a provider must be loader before another one, 
this bundle provides a [PriorityDataProviderInterface](../src/DataProvider/PriorityDataProviderInterface.php)
that allows to define a priority individually, during the loading process, 
the `DocumentLoader` takes this integer and compare every provider in order to load in the desired order.
