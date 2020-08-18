<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
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
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IndexCreatedEvent::class => 'onIndexCreatedEvent',
            IndexRemovedEvent::class => 'onIndexRemovedEvent',
            IndexRetrievedEvent::class => 'onIndexRetrievedEvent',
        ];
    }

    public function onIndexCreatedEvent(IndexCreatedEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been created'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
            'configuration' => $event->getConfig(),
        ]);
    }

    public function onIndexRemovedEvent(IndexRemovedEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been removed'), [
            self::INDEX_LOG_KEY => $event->getUid(),
        ]);
    }

    public function onIndexRetrievedEvent(IndexRetrievedEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'An index has been retrieved'), [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
        ]);
    }
}
