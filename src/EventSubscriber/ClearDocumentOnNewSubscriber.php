<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ClearDocumentOnNewSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SearchResultCacheOrchestratorInterface $searchResultOrchestrator)
    {
    }

    public function onDocumentCreated(): void
    {
        $this->searchResultOrchestrator->clear();
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostDocumentCreationEvent::class => 'onDocumentCreated',
        ];
    }
}
