<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\Synonyms\PostResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PostUpdateSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreUpdateSynonymsEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SynonymsEventSubscriber implements EventSubscriberInterface, MeiliSearchEventSubscriberInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function onPostResetSynonyms(PostResetSynonymsEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'The synonyms have been reset'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
            'update' => $event->getUpdate(),
        ]);
    }

    public function onPreResetSynonyms(PreResetSynonymsEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'The synonyms are about to been reset'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
        ]);
    }

    public function onPostUpdateSynonyms(PostUpdateSynonymsEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'The synonyms have been updated'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
            'update' => $event->getUpdate(),
        ]);
    }

    public function onPreUpdateSynonyms(PreUpdateSynonymsEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'The synonyms are about to been updated'), [
            self::INDEX_LOG_KEY => $event->getIndex(),
            'synonyms' => $event->getSynonyms(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostResetSynonymsEvent::class => 'onPostResetSynonyms',
            PreResetSynonymsEvent::class => 'onPreResetSynonyms',
            PostUpdateSynonymsEvent::class => 'onPostUpdateSynonyms',
            PreUpdateSynonymsEvent::class => 'onPreUpdateSynonyms',
        ];
    }
}
