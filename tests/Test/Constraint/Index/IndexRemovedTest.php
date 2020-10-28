<?php

declare(strict_types=1);

namespace Test\Constraint\Index;

use MeiliSearchBundle\Event\Index\IndexEventList;
use MeiliSearchBundle\Event\Index\IndexRemovedEvent;
use MeiliSearchBundle\Test\Constraint\Index\IndexRemoved;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRemovedTest extends TestCase
{
    public function testIndexRemovedCanBeCounted(): void
    {
        $list = new IndexEventList();
        $list->add(new IndexRemovedEvent('1'));

        $constraint = new IndexRemoved(1);

        static::assertTrue($constraint->evaluate($list, '', true));
        static::assertSame('1 index has been removed', $constraint->toString());
    }
}
