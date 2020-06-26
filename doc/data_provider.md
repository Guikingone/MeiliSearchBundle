# Data provider

Data providers are the main entry point for every document that you may want to add in MeiliSearch.
The main role of a data provider is to return an array that contain the data stored as a document in MS.

In order to see how to use it, let's use Doctrine:

```php
<?php

use MeiliSearchBundle\src\DataProvider\DocumentDataProviderInterface;
// ...

final class PostDataProvider implements DocumentDataProviderInterface
{
    private $postRepository;

    // ... constructor

    public function support() : string
    {
        return 'foo';
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

The idea here is pretty simple, every post is a sub-array of `$collection`, this way,
when you submit the `getDocument()` return value in MS, you link every post to the `foo` index.

Keep in mind that a provider can return data from any source, as long as the array is valid, you're good to go.
