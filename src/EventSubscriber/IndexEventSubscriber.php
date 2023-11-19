<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use MeiliSearchBundle\Event\Index\IndexRetrievedEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexEventSubscriber implements EventSubscriberInterface, MeiliSearchEventSubscriberInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexEventListInterface $eventList,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    public function onIndexCreatedEvent(IndexCreatedEvent $event): void
    {
        $this->eventList->add($event);

        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been created'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
            'configuration' => $event->getConfig(),
        ]);
    }

    public function onIndexRemovedEvent(IndexRemovedEvent $event): void
    {
        $this->eventList->add($event);

        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been removed'), [
            self::INDEX_LOG_KEY => $event->getUid(),
        ]);
    }

    public function onIndexRetrievedEvent(IndexRetrievedEvent $event): void
    {
        $this->eventList->add($event);

        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been retrieved'), [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IndexCreatedEvent::class => 'onIndexCreatedEvent',
            IndexRemovedEvent::class => 'onIndexRemovedEvent',
            IndexRetrievedEvent::class => 'onIndexRetrievedEvent',
        ];
    }
}
