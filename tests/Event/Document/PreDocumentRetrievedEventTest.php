<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Event\Document;

use Generator;
use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Document\PreDocumentRetrievedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentRetrievedEventTest extends TestCase
{
    /**
     * @dataProvider provideIdentifiers
     *
     * @param string|int $id
     */
    public function testDocumentCanBeConfigured($id): void
    {
        $index = $this->createMock(Indexes::class);

        $event = new PreDocumentRetrievedEvent($index, $id);

        static::assertSame($index, $event->getIndex());
        static::assertSame($id, $event->getId());
    }

    public function provideIdentifiers(): Generator
    {
        yield 'string' => ['1'];
        yield 'integer' => [1];
    }
}
