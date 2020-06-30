# Messenger

This bundle allows you to use `Symfony/Messenger` in order to delay some actions.

## Indexes

### Creating an index

Thanks to `AddIndexMessage`, you can easily submit a new index: 

```php
<?php

use MeiliSearchBundle\Messenger\AddIndexMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function action(MessageBusInterface $bus): void 
    {
        // Do some actions

        $bus->dispatch(new AddIndexMessage('foo', 'bar')); // The second argument define the primary key of the related documents, it's optional.
    }
}
```

### Deleting an index

Thanks to `AddIndexMessage`, you can easily delete an index: 

```php
<?php

use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function action(MessageBusInterface $bus): void 
    {
        // Do some actions

        $bus->dispatch(new DeleteIndexMessage('foo'));
    }
}
```

## Documents

### Creating a document

Thanks to `AddDocumentMessage`, you can easily submit new documents in the desired index: 

```php
<?php

use MeiliSearchBundle\Messenger\AddDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function action(MessageBusInterface $bus): void 
    {
        // Do some actions
        $document = [
            [
                'id' => 1,
                'key' => 'foo',
            ],
        ];

        $bus->dispatch(new AddDocumentMessage('foo', $document, 'key')); // The third argument define the primary key of the document, it's optional.
    }
}
```
