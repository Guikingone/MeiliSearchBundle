<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\ExpressionLanguage;

use MeiliSearchBundle\ExpressionLanguage\SearchExpressionLanguage;
use MeiliSearchBundle\Search\Search;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExpressionLanguageTest extends TestCase
{
    public function testSearchExpressionLanguageCanCompileSearch(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();

        static::assertSame('IN "foo" ON "bar" WHERE empty MAX 0', $expressionLanguage->compile('search("foo", "bar")'));
    }

    public function testSearchExpressionLanguageCanCompileSearchWithFilters(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();

        static::assertSame(
            'IN "foo" ON "bar" WHERE "title > 2" MAX 0',
            $expressionLanguage->compile('search("foo", "bar", "title > 2")')
        );
    }

    public function testSearchExpressionLanguageCanCompileSearchWithMax(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();

        static::assertSame(
            'IN "foo" ON "bar" WHERE "" MAX 2',
            $expressionLanguage->compile('search("foo", "bar", "", 2)')
        );
    }

    public function testSearchExpressionLanguageCanHandleSearch(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();
        $search = $expressionLanguage->evaluate('search("foo", "bar")');

        static::assertInstanceOf(Search::class, $search);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());
    }

    public function testSearchExpressionLanguageCanHandleSearchWithFilters(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();
        $search = $expressionLanguage->evaluate('search("foo", "bar", "title > 2", 2)');

        static::assertInstanceOf(Search::class, $search);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());
        static::assertSame('title > 2', $search->getComputedFilters());
        static::assertSame(2, $search->getLimit());
    }

    public function testSearchExpressionLanguageCanHandleSearchWithMax(): void
    {
        $expressionLanguage = new SearchExpressionLanguage();
        $search = $expressionLanguage->evaluate('search("foo", "bar", "", 2)');

        static::assertInstanceOf(Search::class, $search);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());
        static::assertSame(2, $search->getLimit());
    }
}
