# Events

This bundle uses a set of events to allow developers to listen and interact during the whole process.

## Documents

Three events are available during the document lifecycle: 

- `PostDocumentDeletionEvent`: Allow to retrieve the document deletion identifier
- `PostDocumentUpdateEvent`: Allow to retrieve the document update identifier
- `PreDocumentDeletionEvent`: Allow to retrieve the document before the deletion
- `PreDocumentUpdateEvent`: Allow to retrieve the document before the update

## Indexes

Three events are available during the index lifecycle: 

- `IndexCreatedEvent`: Allow to retrieve the `Index` and the related configuration after creating it
- `IndexRemovedEvent`: Allow to retrieve the uid of the deleted index after the deletion
- `IndexRetrievedEvent`: Allow to retrieve the `Index` after retrieving it

## Searches

Two events are available during the search lifecycle: 

- `PostSearchEvent`: Allow to retrieve the `Search`
- `PreSearchEvent`: Allow to retrieve the configuration used to trigger a search
