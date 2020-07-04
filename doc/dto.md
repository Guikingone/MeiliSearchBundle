# DTO / Value Object

This bundle allows you to use DTO's / Value object when fetching documents, 
in its core, `Symfony/Serializer` is used.

## Usage

In order to be returned as a DTO, the FQCN must be passed when creating the document,
if the FQCN is not defined, an array is returned:

```php
<?php

use App\DTO\Foo;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;

// ...

final class FooService
{
    public function __invoke(DocumentEntryPointInterface $documentOrchestrator): string
    {
        // We provide the FQCN as a fourth argument, this one will be merged in the document payload
        $documentOrchestrator->addDocument('foo', [
            'id' => 1,
            'key' => 'bar',
        ], null, Foo::class);
    
        // As we set a DTO during the creation of the document, 
        // the orchestrator automatically map the result into it 
        $dto = $documentOrchestrator->getDocument('foo', 1);
    
        return $dto->key; // return 'bar'
    }
}
```

## Search usage

Using DTO's during a search is just a matter of arguments:

```php
<?php

use App\DTO\Foo;
use MeiliSearchBundle\Search\SearchEntryPointInterface;

// ...

final class FooController
{
    public function __invoke(SearchEntryPointInterface $searchEntryPoint): void
    {
        $search = $searchEntryPoint->search('foo', 'bar', [], true);
        $dto = $search->getHit(0);
        
        $dto->key; // return 'bar'
    }
}
```

Thanks to the fourth argument, `SearchEntryPointInterface` will return a `Search` object where 
every hits is an instance of the related DTO's (if the `model` key has been defined during the population).
