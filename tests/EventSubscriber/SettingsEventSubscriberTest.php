<?php

declare(strict_types=1);

namespace EventSubscriber;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use MeiliSearchBundle\EventSubscriber\SettingsEventSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SettingsEventSubscriberTest extends TestCase
{
    public function testSubscriberIsConfigured(): void
    {
        static::assertArrayHasKey(PostSettingsUpdateEvent::class, SettingsEventSubscriber::getSubscribedEvents());
        static::assertSame('onPostSettingsUpdateEvent', SettingsEventSubscriber::getSubscribedEvents()[PostSettingsUpdateEvent::class]);

        static::assertArrayHasKey(PreSettingsUpdateEvent::class, SettingsEventSubscriber::getSubscribedEvents());
        static::assertSame('onPreSettingsUpdateEvent', SettingsEventSubscriber::getSubscribedEvents()[PreSettingsUpdateEvent::class]);
    }

    public function testPostSettingsUpdateEventCanBeSubscribed(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('[MeiliSearch] Settings have been updated'), [
            'index' => 'foo',
            'update' => 1,
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PostSettingsUpdateEvent($index, 1);

        $subscriber = new SettingsEventSubscriber($logger);
        $subscriber->onPostSettingsUpdateEvent($event);
    }

    public function testPreSettingsUpdateEventCanBeSubscribed(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info')->with(self::equalTo('[MeiliSearch] Settings are about to be updated'), [
            'index' => 'foo',
            'update' => ['rankingRules' => []],
        ]);

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $event = new PreSettingsUpdateEvent($index, [
            'rankingRules' => [],
        ]);

        $subscriber = new SettingsEventSubscriber($logger);
        $subscriber->onPreSettingsUpdateEvent($event);
    }
}
