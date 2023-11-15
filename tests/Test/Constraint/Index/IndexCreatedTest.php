<?php

declare(strict_types=1);

namespace Test\Constraint\Index;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Event\Index\IndexCreatedEvent;
use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Test\Constraint\Index\IndexCreated;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedTest extends TestCase
{
    public function testIndexCreatedCanBeCounted(): void
    {
        $index = $this->createMock(Indexes::class);

        $list = new IndexEventList();
        $list->add(new IndexCreatedEvent([], $index));

        $constraint = new IndexCreated(1);

        static::assertTrue($constraint->evaluate($list, '', true));
        static::assertSame('1 index has been created', $constraint->toString());
    }
}
