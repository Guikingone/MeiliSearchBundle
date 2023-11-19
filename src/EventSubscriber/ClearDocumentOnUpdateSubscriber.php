<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ClearDocumentOnUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SearchResultCacheOrchestratorInterface $searchResultOrchestrator)
    {
    }

    public function onDocumentUpdated(): void
    {
        $this->searchResultOrchestrator->clear();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostDocumentUpdateEvent::class => 'onDocumentUpdated',
        ];
    }
}
