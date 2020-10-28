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
    /**
     * @var SearchResultCacheOrchestratorInterface
     */
    private $searchResultOrchestrator;

    public function __construct(SearchResultCacheOrchestratorInterface $searchResultOrchestrator)
    {
        $this->searchResultOrchestrator = $searchResultOrchestrator;
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
