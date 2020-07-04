<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\EventSubscriber;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Synonyms\PostResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PostUpdateSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreResetSynonymsEvent;
use MeiliSearchBundle\Event\Synonyms\PreUpdateSynonymsEvent;
use MeiliSearchBundle\EventSubscriber\SynonymsEventSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SynonymsEventSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(PostResetSynonymsEvent::class, SynonymsEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PostUpdateSynonymsEvent::class, SynonymsEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreResetSynonymsEvent::class, SynonymsEventSubscriber::getSubscribedEvents());
        static::assertArrayHasKey(PreUpdateSynonymsEvent::class, SynonymsEventSubscriber::getSubscribedEvents());
    }

    public function testSubscriberCanListenOnPostResetWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PostResetSynonymsEvent($index, 1);

        $subscriber = new SynonymsEventSubscriber();
        $subscriber->onPostResetSynonyms($event);
    }

    public function testSubscriberCanListenOnPostReset(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PostResetSynonymsEvent($index, 1);

        $subscriber = new SynonymsEventSubscriber($logger);
        $subscriber->onPostResetSynonyms($event);
    }

    public function testSubscriberCanListenOnPostUpdateWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PostUpdateSynonymsEvent($index, 1);

        $subscriber = new SynonymsEventSubscriber();
        $subscriber->onPostUpdateSynonyms($event);
    }

    public function testSubscriberCanListenOnPostUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PostUpdateSynonymsEvent($index, 1);

        $subscriber = new SynonymsEventSubscriber($logger);
        $subscriber->onPostUpdateSynonyms($event);
    }

    public function testSubscriberCanListenOnPreResetWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PreResetSynonymsEvent($index);

        $subscriber = new SynonymsEventSubscriber();
        $subscriber->onPreResetSynonyms($event);
    }

    public function testSubscriberCanListenOnPreReset(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PreResetSynonymsEvent($index);

        $subscriber = new SynonymsEventSubscriber($logger);
        $subscriber->onPreResetSynonyms($event);
    }

    public function testSubscriberCanListenOnPreUpdateWithoutLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PreUpdateSynonymsEvent($index, []);

        $subscriber = new SynonymsEventSubscriber();
        $subscriber->onPreUpdateSynonyms($event);
    }

    public function testSubscriberCanListenOnPreUpdate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);

        $event = new PreUpdateSynonymsEvent($index, []);

        $subscriber = new SynonymsEventSubscriber($logger);
        $subscriber->onPreUpdateSynonyms($event);
    }
}
