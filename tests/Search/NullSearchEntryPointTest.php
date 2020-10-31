<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Search\NullSearchEntryPoint;
use MeiliSearchBundle\Search\SearchResultInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class NullSearchEntryPointTest extends TestCase
{
    public function testEntryPointReturnResult(): void
    {
        $entryPoint = new NullSearchEntryPoint();

        $result = $entryPoint->search('foo', 'title');

        static::assertInstanceOf(SearchResultInterface::class, $result);
        static::assertEmpty($result->getHits());
    }
}
