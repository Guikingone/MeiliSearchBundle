<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexList;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexListTest extends TestCase
{
    public function testArrayCanBeAdded(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $secondIndex = $this->createMock(Indexes::class);
        $secondIndex->expects(self::once())->method('getUid')->willReturn('bar');

        $list = new IndexList([$index, $secondIndex]);
        static::assertSame(2, $list->count());
    }

    public function testIndexCountCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $list = new IndexList();

        static::assertSame(0, $list->count());

        $list->add($index);
        static::assertSame(1, $list->count());
    }

    public function testIndexCanBeFound(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $list = new IndexList();

        static::assertFalse($list->has('foo'));

        $list->add($index);
        static::assertTrue($list->has('foo'));
    }

    public function testIndexCanBeRemoved(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $list = new IndexList();
        $list->remove('foo');

        static::assertSame(0, $list->count());

        $list->add($index);
        static::assertSame(1, $list->count());

        $list->remove('foo');
        static::assertSame(0, $list->count());
    }

    public function testIndexCanBeFiltered(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::exactly(2))->method('getUid')->willReturn('foo');

        $secondIndex = $this->createMock(Indexes::class);
        $secondIndex->expects(self::once())->method('getUid')->willReturn('bar');

        $list = new IndexList([$index, $secondIndex]);
        $list = $list->filter(function (Indexes $index, string $_): bool {
            return $_ === 'foo';
        });

        static::assertNotEmpty($list);
        static::assertEquals([
            'foo' => $index,
        ], $list->toArray());
    }

    public function testIndexListCanBeIterated(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getUid')->willReturn('foo');

        $list = new IndexList([$index]);

        foreach ($list as $index) {
            static::assertInstanceOf(Indexes::class, $index);
        }
    }
}
