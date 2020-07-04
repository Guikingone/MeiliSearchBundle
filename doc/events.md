# Events

This bundle uses a set of events to allow developers to listen and interact during the whole process.

## Documents

Three events are dispatched during the document lifecycle: 

### Pre action events

- [PreDocumentCreationEvent](../src/Event/Document/PreDocumentCreationEvent.php): Allow to retrieve the index and the document before the creation.
- [PreDocumentDeletionEvent](../src/Event/Document/PreDocumentDeletionEvent.php): Allow to retrieve the document and the index before removing the document, by default, the document is fetched before being deleted. This event is not triggered when a set of documents is deleted.
- [PreDocumentRetrievedEvent](../src/Event/Document/PreDocumentRetrievedEvent.php): Allow to retrieve the document identifier and the index before fetching it. 
- [PreDocumentUpdateEvent](../src/Event/Document/PreDocumentUpdateEvent.php): Allow to retrieve the document before the update.

### Post action events

- [PostDocumentCreationEvent](../src/Event/Document/PostDocumentCreationEvent.php): Allow to retrieve the update identifier when a document has been created.
- [PostDocumentDeletionEvent](../src/Event/Document/PostDocumentDeletionEvent.php): Allow to retrieve the document deletion identifier.
- [PostDocumentRetrievedEvent](../src/Event/Document/PostDocumentRetrievedEvent.php): Allow to retrieve the document and the index before returning the document, by default, the document is returned as stored in MeiliSearch.
- [PostDocumentUpdateEvent](../src/Event/Document/PostDocumentUpdateEvent.php): Allow to retrieve the document update identifier.

## Indexes

Three events are dispatched during the index lifecycle: 

- [IndexCreatedEvent](../src/Event/Index/IndexCreatedEvent.php): Allow to retrieve the `Index` and the related configuration after creating it
- [IndexRemovedEvent](../src/Event/Index/IndexRemovedEvent.php): Allow to retrieve the uid of the deleted index after the deletion
- [IndexRetrievedEvent](../src/Event/Index/IndexRetrievedEvent.php): Allow to retrieve the `Index` after retrieving it

## Searches

Two events are dispatched during the search lifecycle: 

- [PreSearchEvent](../src/Event/PreSearchEvent.php): Allow to retrieve the configuration used to trigger a search.
- [PostSearchEvent](../src/Event/PostSearchEvent.php): Allow to retrieve the `Search` instance which contain the result of the search.

## Synonyms

Four events are dispatched during synonyms lifecyle:

### Update events

- [PreUpdateSynonymsEvent](../src/Event/Synonyms/PreUpdateSynonymsEvent.php): Allow to retrieve the index and the new synonyms before updating the synonyms.
- [PostUpdateSynonymsEvent](../src/Event/Synonyms/PostUpdateSynonymsEvent.php): Allow to retrieve the index and the update identifier after updating the synonyms.

### Reset events

- [PreResetSynonymsEvent](../src/Event/Synonyms/PreResetSynonymsEvent.php): Allow to retrieve the index before resetting the synonyms.
- [PostResetSynonymsEvent](../src/Event/Synonyms/PostResetSynonymsEvent.php): Allow to retrieve the index and the update identifier after resetting the synonyms.

## Note

Each event is listened and trigger an `info()` log when dispatched.
