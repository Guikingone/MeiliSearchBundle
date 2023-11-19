<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Search\SearchResult;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEventTest extends TestCase
{
    public function testResultIsAccessible(): void
    {
        $event = new PostSearchEvent(SearchResult::create([], 0, 0, 0, false, 0, 'foo'));

        static::assertInstanceOf(SearchResult::class, $event->getResult());
    }
}
