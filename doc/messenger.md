# Messenger

This bundle allows you to use `Symfony/Messenger` in order to delay some actions.

## Index(es) creation

Thanks to `AddIndexMessage`, you can easily submit new indexes: 

```php
<?php

use MeiliSearchBundle\src\Messenger\AddIndexMessage;
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

## Document(s) creation

Thanks to `AddDocumentMessage`, you can easily submit new documents in the desired index: 

```php
<?php

use MeiliSearchBundle\src\Messenger\AddDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function action(MessageBusInterface $bus): void 
    {
        // Do some actions
        $index = 'foo';
        $document = [
            [
                'id' => 1,
                'key' => 'foo',
            ],
        ];

        $bus->dispatch(new AddDocumentMessage($index, $document, 'key')); // The third argument define the primary key of the document, it's optional.
    }
}
```
