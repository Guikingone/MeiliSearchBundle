<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SettingsEventSubscriber implements EventSubscriberInterface, MeiliSearchEventSubscriberInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexEventListInterface $eventList,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostSettingsUpdateEvent::class => 'onPostSettingsUpdateEvent',
            PreSettingsUpdateEvent::class => 'onPreSettingsUpdateEvent',
        ];
    }

    public function onPostSettingsUpdateEvent(PostSettingsUpdateEvent $event): void
    {
        $this->eventList->add($event);

        $this->logger->info(sprintf(self::LOG_MASK, 'Settings have been updated'), [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::UPDATE_LOG_KEY => $event->getUpdate(),
        ]);
    }

    public function onPreSettingsUpdateEvent(PreSettingsUpdateEvent $event): void
    {
        $this->eventList->add($event);

        $this->logger->info(sprintf(self::LOG_MASK, 'Settings are about to be updated'), [
            self::INDEX_LOG_KEY => $event->getIndex()->getUid(),
            self::UPDATE_LOG_KEY => $event->getUpdatePayload(),
        ]);
    }
}
