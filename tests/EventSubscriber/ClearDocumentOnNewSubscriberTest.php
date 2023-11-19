<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use MeiliSearchBundle\Event\Document\PostDocumentCreationEvent;
use MeiliSearchBundle\EventSubscriber\ClearDocumentOnNewSubscriber;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ClearDocumentOnNewSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(
            PostDocumentCreationEvent::class,
            ClearDocumentOnNewSubscriber::getSubscribedEvents()
        );
        static::assertSame(
            'onDocumentCreated',
            ClearDocumentOnNewSubscriber::getSubscribedEvents()[PostDocumentCreationEvent::class]
        );
    }

    public function testCacheCanBeCleared(): void
    {
        $orchestrator = $this->createMock(SearchResultCacheOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('clear');

        $subscriber = new ClearDocumentOnNewSubscriber($orchestrator);
        $subscriber->onDocumentCreated();
    }
}
