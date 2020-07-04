<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\EventSubscriber;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReaderInterface;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Bridge\Doctrine\EventSubscriber\DocumentSubscriber;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataRegistry;
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
        $serializer = $this->createMock(SerializerInterface::class);

        $subscriber = new DocumentSubscriber($orchestrator, $reader, new IndexMetadataRegistry(), $propertyAccessor, $serializer);

        static::assertContainsEquals(Events::postPersist, $subscriber->getSubscribedEvents());
        static::assertContainsEquals(Events::postUpdate, $subscriber->getSubscribedEvents());
        static::assertContainsEquals(Events::postRemove, $subscriber->getSubscribedEvents());
    }

    public function testSubscriberCannotStoreInvalidDocument(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, new IndexMetadataRegistry(), $propertyAccessor, $serializer);
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCanStoreValidDocument(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', false));

        $reader = new DocumentReader(new AnnotationReader());

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer);
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCanStoreValidDocumentWithMessageBus(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', true));

        $reader = new DocumentReader(new AnnotationReader());

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

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer, $messageBus);
        $subscriber->postPersist($lifeCycleEventArgs);
    }

    public function testSubscriberCannotUpdateInvalidDocument(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, new IndexMetadataRegistry(), $propertyAccessor, $serializer);
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCanUpdateValidDocument(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', false));

        $reader = new DocumentReader(new AnnotationReader());

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('normalize')->willReturn([
            'id' => 1,
            'title' => 'bar',
        ]);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer);
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCanUpdateValidDocumentWithMessageBus(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', true));

        $reader = new DocumentReader(new AnnotationReader());

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::never())->method('updateDocument');

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

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer, $messageBus);
        $subscriber->postUpdate($lifeCycleEventArgs);
    }

    public function testSubscriberCannotRemoveInvalidDocument(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $reader = $this->createMock(DocumentReaderInterface::class);
        $reader->expects(self::never())->method('getConfiguration');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $serializer = $this->createMock(SerializerInterface::class);

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new stdClass());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, new IndexMetadataRegistry(), $propertyAccessor, $serializer);
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocument(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', false));

        $reader = new DocumentReader(new AnnotationReader());

        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::once())->method('getValue')->willReturn(1);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('normalize');

        $lifeCycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lifeCycleEventArgs->expects(self::once())->method('getObject')->willReturn(new Foo());

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer);
        $subscriber->postRemove($lifeCycleEventArgs);
    }

    public function testSubscriberCanRemoveValidDocumentWithMessageBus(): void
    {
        $indexMetadataRegistry = new IndexMetadataRegistry();
        $indexMetadataRegistry->add('foo', new IndexMetadata('foo', true));

        $reader = new DocumentReader(new AnnotationReader());

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

        $subscriber = new DocumentSubscriber($orchestrator, $reader, $indexMetadataRegistry, $propertyAccessor, $serializer, $messageBus);
        $subscriber->postRemove($lifeCycleEventArgs);
    }
}

/**
 * @Document(index="foo", primaryKey="id")
 */
final class Foo
{
}
