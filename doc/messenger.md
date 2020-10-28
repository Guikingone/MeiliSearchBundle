# Messenger

This bundle allows you to use `Symfony/Messenger` in order to delay some actions.

Most of the MeiliSearch API actions can be performed thanks to a message, here's the full list:

### Indexes

- [AddIndexMessage](../src/Messenger/AddIndexMessage.php)
- [DeleteIndexMessage](../src/Messenger/DeleteIndexMessage.php)

### Documents

- [AddDocumentMessage](../src/Messenger/Document/AddDocumentMessage.php)
- [DeleteDocumentMessage](../src/Messenger/Document/DeleteDocumentMessage.php)
- [UpdateDocumentMessage](../src/Messenger/Document/UpdateDocumentMessage.php)

### Synonyms

- [ResetSynonymsMessage](../src/Messenger/Synonyms/ResetSynonymsMessage.php)
- [UpdateSynonymsMessage](../src/Messenger/Synonyms/UpdateSynonymsMessage.php)

## Usage

### Indexes

#### Creating an index

Thanks to [AddIndexMessage](../src/Messenger/AddIndexMessage.php), you can easily submit a new index: 

```php
<?php

use MeiliSearchBundle\Messenger\AddIndexMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
    {
        // Do some actions

        $bus->dispatch(new AddIndexMessage('foo', 'bar')); // The second argument define the primary key of the related documents, it's optional.
    }
}
```

#### Extra configuration

The `AddIndexMessage` allows you to configure extra information thanks to the third `$configuration` attribute:

- **searchableAttributes**: An array of fields that can be used to trigger a search,
by default, every attribute found in the document is "searchable".

- **displayedAttributes**: An array of fields that are displayed for each matching documents,
by default, every attribute found in the document is displayed in the search result.

### Deleting an index

Thanks to [DeleteIndexMessage](../src/Messenger/DeleteIndexMessage.php), you can easily delete an index: 

```php
<?php

use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
    {
        // Do some actions

        $bus->dispatch(new DeleteIndexMessage('foo'));
    }
}
```

## Documents

### Creating a document

Thanks to [AddDocumentMessage](../src/Messenger/Document/AddDocumentMessage.php), you can easily submit new documents in the desired index: 

```php
<?php

use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
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

### Deleting a document

Thanks to [DeleteDocumentMessage](../src/Messenger/Document/DeleteDocumentMessage.php), you can easily delete a document: 

```php
<?php

use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
    {
        // Do some actions

        $bus->dispatch(new DeleteDocumentMessage('foo', 1));
    }
}
```

### Updating a document

// TODO

## Synonyms

### Updating a set of synonyms

Thanks to [UpdateSynonymsMessage](../src/Messenger/Synonyms/UpdateSynonymsMessage.php), you can update the synonyms of an index:

```php
<?php

use MeiliSearchBundle\Messenger\Synonyms\UpdateSynonymsMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
    {
        $bus->dispatch(new UpdateSynonymsMessage('movies', [
            'xmen' => ['logan', 'wolverine'],
        ]));
    }
}
```

### Resetting the synonyms

Thanks to [ResetSynonymsMessage](../src/Messenger/Synonyms/ResetSynonymsMessage.php), you can reset the synonyms of an index:

```php
<?php

use MeiliSearchBundle\Messenger\Synonyms\ResetSynonymsMessage;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooService
{
    public function __invoke(MessageBusInterface $bus): void 
    {
        $bus->dispatch(new ResetSynonymsMessage('movies'));
    }
}
```

## Routing

Each message defined by the bundle implements `MessageInterface`, 
if you need to route every message to a specific transport, 
here's the easiest way to do:

```yaml
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%"

        routing:
            'MeiliSearchBundle\Messenger\MessageInterface':  async
``` 

**Note**: Of course, you can decide to route a specific message to specific transport:

```yaml
framework:
    messenger:
        transports:
            async: "%env(MESSENGER_TRANSPORT_DSN)%"

        routing:
            'MeiliSearchBundle\Messenger\AddIndexMessage':  async
``` 
