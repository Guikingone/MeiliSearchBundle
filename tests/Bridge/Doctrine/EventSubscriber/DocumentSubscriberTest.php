<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReaderInterface;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use MeiliSearchBundle\Metadata\IndexMetadataInterface;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        /** @var Serializer $serializer */
        $serializer = $this->createMock(SerializerInterface::class);
        $metadataRegistry = $this->createMock(IndexMetadataRegistryInterface::class);

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $metadataRegistry, $propertyAccessor, $serializer);

        static::assertContainsEquals(Events::postPersist, $subscriber->getSubscribedEvents());
        static::assertContainsEquals(Events::postUpdate, $subscriber->getSubscribedEvents());
        static::assertContainsEquals(Events::postRemove, $subscriber->getSubscribedEvents());
    }

    public function testSubscriberCannotStoreInvalidDocument(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        /** @var Serializer $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $registry, $propertyAccessor, $serializer);
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCanStoreValidDocument(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get');

        $reader = new DocumentReader();

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCanStoreValidDocumentWithMessageBus(): void
    {
        $metadata = $this->createMock(IndexMetadataInterface::class);
        $metadata->expects(self::once())->method('isAsync')->willReturn(true);

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get')->willReturn($metadata);

        $reader = new DocumentReader();

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::never())->method('addDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCannotUpdateInvalidDocument(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::never())->method('get');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        /** @var Serializer $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $registry, $propertyAccessor, $serializer);
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCanUpdateValidDocument(): void
    {
        $metadata = $this->createMock(IndexMetadataInterface::class);
        $metadata->expects(self::once())->method('isAsync')->willReturn(false);
        $metadata->expects(self::once())->method('getUid')->willReturn('foo');
        $metadata->expects(self::once())->method('getPrimaryKey')->willReturn('id');

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get')->willReturn($metadata);

        $reader = new DocumentReader();

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('updateDocument')->with(self::equalTo('foo'), [
            'id' => 1,
            'title' => 'bar',
        ], self::equalTo('id'));

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCanUpdateValidDocumentWithMessageBus(): void
    {
        $metadata = $this->createMock(IndexMetadataInterface::class);
        $metadata->expects(self::once())->method('isAsync')->willReturn(true);

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get')->willReturn($metadata);

        $reader = new DocumentReader();

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::never())->method('updateDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())->method('dispatch')
            ->with(
                new UpdateDocumentMessage('foo', [
                    'id' => 1,
                    'title' => 'bar',
                ], 'id')
            )
            ->willReturn(new Envelope(new stdClass()));

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCanUpdateValidDocumentWithoutMessageBus(): void
    {
        $metadata = $this->createMock(IndexMetadataInterface::class);
        $metadata->expects(self::once())->method('isAsync')->willReturn(true);

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get')->willReturn($metadata);

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('updateDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch');

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $reader = new DocumentReader();

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $registry, $propertyAccessor, $serializer);
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCannotRemoveInvalidDocument(): void
    {
        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::never())->method('get');

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        /** @var Serializer $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $registry, $propertyAccessor, $serializer);
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocument(): void
    {
        $document = new Foo();

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get');

        $reader = new DocumentReader();

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')->with($document, 'id')->willReturn(1);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('normalize');

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn($document);

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $registry, $propertyAccessor, $serializer);
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocumentWithoutPrimaryKey(): void
    {
        $document = new Foo();

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get');

        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::once())->method('isDocument')->with($document)->willReturn(true);
        $reader->expects(self::once())->method('getConfiguration')->with($document)->willReturn(
            new Document(
                index: 'foo',
            )
        );

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')->with($document, 'id')->willReturn(1);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('normalize');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn($document);

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocumentWithPrimaryKey(): void
    {
        $document = new Foo();

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get');

        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::once())->method('isDocument')->with($document)->willReturn(true);
        $reader->expects(self::once())->method('getConfiguration')
            ->with($document)
            ->willReturn(
                new Document(
                    index: 'foo',
                    primaryKey: 'title',
                )
            );

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')
            ->with(self::equalTo($document), self::equalTo('title'))
            ->willReturn(1);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('normalize');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::never())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn($document);

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocumentWithMessageBus(): void
    {
        $metadata = $this->createMock(IndexMetadataInterface::class);
        $metadata->expects(self::once())->method('isAsync')->willReturn(true);

        $registry = $this->createMock(IndexMetadataRegistryInterface::class);
        $registry->expects(self::once())->method('get')->willReturn($metadata);

        $reader = new DocumentReader();

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::never())->method('removeDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')->willReturn(1);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects(self::once())->method('dispatch')->willReturn(new Envelope(new stdClass()));

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('normalize');

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber(
            $orchestrator,
            $reader,
            $registry,
            $propertyAccessor,
            $serializer,
            $messageBus
        );
        $subscriber->postRemove($lifeCycleEventArgs);
    }
}

#[Document(index: 'foo', primaryKey: 'id')]
final class Foo
{
}
