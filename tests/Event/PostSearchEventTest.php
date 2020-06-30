<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event;

use MeiliSearchBundle\Search\Search;
use MeiliSearchBundle\Event\PostSearchEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEventTest extends TestCase
{
    public function testResultIsAccessible(): void
    {
        $event = new PostSearchEvent(Search::create([], 0, 0, 0, 0, 0, 'foo'));

        static::assertInstanceOf(Search::class, $event->getResult());
    }
}
