<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReaderInterface;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function get_class;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentSubscriber implements EventSubscriber
{
    /**
     * @var DocumentEntryPointInterface
     */
    private $documentOrchestrator;

    /**
     * @var DocumentReaderInterface
     */
    private $documentReader;

    /**
     * @var IndexMetadataRegistry
     */
    private $indexMetadataRegistry;

    /**
     * @var MessageBusInterface|null
     */
    private $messageBus;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        DocumentEntryPointInterface $documentOrchestrator,
        DocumentReaderInterface $documentReader,
        IndexMetadataRegistry $indexMetadataRegistry,
        PropertyAccessorInterface $propertyAccessor,
        SerializerInterface $serializer,
        ?MessageBusInterface $messageBus = null
    ) {
        $this->documentOrchestrator = $documentOrchestrator;
        $this->documentReader = $documentReader;
        $this->indexMetadataRegistry = $indexMetadataRegistry;
        $this->propertyAccessor = $propertyAccessor;
        $this->serializer = $serializer;
        $this->messageBus = $messageBus;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if (!$this->documentReader->isDocument($document)) {
            return;
        }

        $configuration = $this->documentReader->getConfiguration($document);
        $documentBody = $this->serializer->normalize($document);

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(new AddDocumentMessage(
                $indexMetadata->getUid(),
                $documentBody,
                $indexMetadata->getPrimaryKey(),
                $configuration->getModel() ? get_class($document) : null
            ));

            return;
        }

        $this->documentOrchestrator->addDocument(
            $indexMetadata->getUid(),
            $documentBody,
            $indexMetadata->getPrimaryKey(),
            $configuration->getModel() ? get_class($document) : null
        );
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if (!$this->documentReader->isDocument($document)) {
            return;
        }

        $configuration = $this->documentReader->getConfiguration($document);
        $documentBody = $this->serializer->normalize($document);

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(new UpdateDocumentMessage(
                $configuration->getIndex(),
                [$documentBody],
                $configuration->getPrimaryKey()
            ));

            return;
        }

        $this->documentOrchestrator->updateDocument(
            $indexMetadata->getUid(),
            [$documentBody],
            $indexMetadata->getPrimaryKey()
        );
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if (!$this->documentReader->isDocument($document)) {
            return;
        }

        $configuration = $this->documentReader->getConfiguration($document);
        $identifier = $this->propertyAccessor->getValue(
            $document,
            null === $configuration->getPrimaryKey() ? $configuration->getPrimaryKey() : 'id'
        );

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(new DeleteDocumentMessage(
                $configuration->getIndex(),
                $identifier
            ));

            return;
        }

        $this->documentOrchestrator->removeDocument($indexMetadata->getUid(), $identifier);
    }
}
