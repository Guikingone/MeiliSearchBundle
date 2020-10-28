<?php

declare(strict_types=1);

namespace Test\Constraint;

use MeiliSearchBundle\Event\PostSearchEvent;
use MeiliSearchBundle\Event\SearchEventList;
use MeiliSearchBundle\Search\SearchResultInterface;
use MeiliSearchBundle\Test\Constraint\Search;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchTest extends TestCase
{
    public function testSearchCanCount(): void
    {
        $result = $this->createMock(SearchResultInterface::class);

        $list = new SearchEventList();
        $list->add(new PostSearchEvent($result));

        $constraint = new Search(1);

        static::assertTrue($constraint->evaluate($list, '', true));
        static::assertSame('1 search has been made', $constraint->toString());
    }
}
