<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReaderInterface;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly DocumentEntryPointInterface $documentEntryPoint,
        private readonly DocumentReaderInterface $documentReader,
        private readonly IndexMetadataRegistryInterface $indexMetadataRegistry,
        private readonly PropertyAccessorInterface $propertyAccessor,
        /** @var Serializer $serializer */
        private readonly SerializerInterface $serializer,
        private readonly ?MessageBusInterface $messageBus = null
    ) {
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

        /** @var Document $configuration */
        $configuration = $this->documentReader->getConfiguration($document);
        /** @var array<string, mixed> $documentBody */
        $documentBody = $this->serializer->normalize($document);

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(
                new AddDocumentMessage(
                    $indexMetadata->getUid(),
                    $documentBody,
                    $indexMetadata->getPrimaryKey(),
                    $configuration->getModel() ? $document::class : null
                )
            );

            return;
        }

        $this->documentEntryPoint->addDocument(
            $indexMetadata->getUid(),
            $documentBody,
            $indexMetadata->getPrimaryKey(),
            $configuration->getModel() ? $document::class : null
        );
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if (!$this->documentReader->isDocument($document)) {
            return;
        }

        /** @var Document $configuration */
        $configuration = $this->documentReader->getConfiguration($document);
        /** @var array<string, bool|int|string> $documentBody */
        $documentBody = $this->serializer->normalize($document);

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(
                new UpdateDocumentMessage(
                    $configuration->getIndex(),
                    $documentBody,
                    $configuration->getPrimaryKey()
                )
            );

            return;
        }

        $this->documentEntryPoint->updateDocument(
            $indexMetadata->getUid(),
            $documentBody,
            $indexMetadata->getPrimaryKey()
        );
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $document = $args->getObject();
        if (!$this->documentReader->isDocument($document)) {
            return;
        }

        /** @var Document $configuration */
        $configuration = $this->documentReader->getConfiguration($document);
        $identifier = $this->propertyAccessor->getValue(
            $document,
            $configuration->getPrimaryKey() ?? 'id'
        );

        $indexMetadata = $this->indexMetadataRegistry->get($configuration->getIndex());
        if ($indexMetadata->isAsync() && null !== $this->messageBus) {
            $this->messageBus->dispatch(
                new DeleteDocumentMessage(
                    $configuration->getIndex(),
                    $identifier
                )
            );

            return;
        }

        $this->documentEntryPoint->removeDocument($indexMetadata->getUid(), $identifier);
    }
}
