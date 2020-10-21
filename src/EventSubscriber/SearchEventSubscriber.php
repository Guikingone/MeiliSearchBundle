<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\PreSearchEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEventSubscriber implements EventSubscriberInterface, MeiliSearchEventSubscriberInterface
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
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostSearchEvent::class => 'onPostSearchEvent',
            PreSearchEvent::class => 'onPreSearchEvent',
        ];
    }

    public function onPostSearchEvent(PostSearchEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'A search has been made'), [
            'results' => $event->getResult()->toArray(),
        ]);
    }

    public function onPreSearchEvent(PreSearchEvent $event): void
    {
        $this->logger->info(sprintf(self::LOG_MASK, 'A search is about to be made'), [
            'configuration' => $event->getConfiguration(),
        ]);
    }
}
