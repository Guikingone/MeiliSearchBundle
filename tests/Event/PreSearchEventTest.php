<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event;

use MeiliSearchBundle\Event\PreSearchEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSearchEventTest extends TestCase
{
    public function testEventCanBeConfigured(): void
    {
        $event = new PreSearchEvent([
            'index' => 'foo',
            'query' => 'bar',
            'options' => [],
            'models' => false,
        ]);

        static::assertNotEmpty($event->getConfiguration());
        static::assertSame('foo', $event->getSpecificConfiguration('index'));
        static::assertSame('bar', $event->getSpecificConfiguration('query'));
        static::assertEmpty($event->getSpecificConfiguration('options'));
        static::assertFalse($event->getSpecificConfiguration('models'));
    }
}
