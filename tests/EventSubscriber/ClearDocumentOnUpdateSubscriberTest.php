<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Event\Document\PostDocumentUpdateEvent;
use MeiliSearchBundle\EventSubscriber\ClearDocumentOnUpdateSubscriber;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ClearDocumentOnUpdateSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(
            PostDocumentUpdateEvent::class,
            ClearDocumentOnUpdateSubscriber::getSubscribedEvents()
        );
        static::assertSame(
            'onDocumentUpdated',
            ClearDocumentOnUpdateSubscriber::getSubscribedEvents()[PostDocumentUpdateEvent::class]
        );
    }

    public function testCacheCanBeCleared(): void
    {
        $orchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('clear');

        $subscriber = new ClearDocumentOnUpdateSubscriber($orchestrator);
        $subscriber->onDocumentUpdated();
    }
}
