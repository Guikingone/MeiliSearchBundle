<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use MeiliSearchBundle\Exception\InvalidSearchConfigurationException;
use MeiliSearchBundle\Search\Search;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchTest extends TestCase
{
    public function testSearchCanBeBuiltOnIndex(): void
    {
        $search = new Search();

        $search->in('foo');
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame('foo', $search->getIndex());

        $search = Search::within('foo');
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame('foo', $search->getIndex());
    }

    public function testSearchCanBeBuiltOnSpecificQuery(): void
    {
        $search = new Search();

        $search->in('foo')->query('bar');
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame('bar', $search->getRaw()['query']);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());

        $search = Search::within('foo')->query('bar');
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame('bar', $search->getRaw()['query']);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());

        $search = Search::on('foo', 'bar');
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame('bar', $search->getRaw()['query']);
        static::assertSame('foo', $search->getIndex());
        static::assertSame('bar', $search->getQuery());
    }

    public function testSearchCanTargetSpecificIndexAndLimitResults(): void
    {
        $search = new Search();
        $search->in('foo')->max(10);

        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame(10, $search->getRaw()['limit']);
        static::assertSame(10, $search->getLimit());

        $search = Search::within('foo')->max(10);
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame(10, $search->getRaw()['limit']);
        static::assertSame(10, $search->getLimit());
    }

    public function testSearchCanTargetSpecificIndexAndOffset(): void
    {
        $search = new Search();
        $search->in('foo')->offset(10);

        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame(10, $search->getRaw()['offset']);

        $search = Search::within('foo')->offset(10);
        static::assertSame('foo', $search->getRaw()['index']);
        static::assertSame(10, $search->getRaw()['offset']);
    }

    public function testSearchCannotBeBuiltWithInvalidWhereCondition(): void
    {
        $search = new Search();
        static::expectException(InvalidSearchConfigurationException::class);
        $search->in('foo')->where('id', '===', 1);

        static::expectException(InvalidSearchConfigurationException::class);
        Search::within('foo')->where('id', '===', 1);
    }

    public function testSearchCanBeBuiltWithValidWhereCondition(): void
    {
        $search = new Search();
        $search->in('foo')->where('id', '=', 1);
        static::assertSame('id = 1', $search->getRaw()['filters']);

        $search = Search::within('foo')->where('id', '=', 1);
        static::assertSame('id = 1', $search->getRaw()['filters']);
    }

    public function testSearchCanBeBuiltWithCompoundWhereCondition(): void
    {
        $search = new Search();
        $search->in('foo')->where('id', '=', 'Hello World');
        static::assertSame('id = "Hello World"', $search->getRaw()['filters']);

        $search = Search::within('foo')->where('id', '=', 'Hello World');
        static::assertSame('id = "Hello World"', $search->getRaw()['filters']);
    }

    public function testSearchCannotBeBuiltWithInvalidAndWhereCondition(): void
    {
        $search = new Search();

        static::expectException(InvalidSearchConfigurationException::class);
        $search->in('foo')->where('id', '=', 1)->andWhere('title', '!==', 'Random');

        static::expectException(InvalidSearchConfigurationException::class);
        Search::within('foo')->where('id', '=', 1)->andWhere('title', '!==', 'Random');
    }

    public function testSearchCanBeBuiltWithValidAndWhereCondition(): void
    {
        $search = new Search();
        $search->in('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random');
        static::assertSame('id = 1 AND title != Random', $search->getRaw()['filters']);

        $search = Search::within('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random');
        static::assertSame('id = 1 AND title != Random', $search->getRaw()['filters']);
    }

    public function testSearchCannotBeBuiltWithInvalidOrWhereCondition(): void
    {
        $search = new Search();

        static::expectException(InvalidSearchConfigurationException::class);
        $search->in('foo')->where('id', '=', 1)->orWhere('title', '!==', 'Random');

        static::expectException(InvalidSearchConfigurationException::class);
        Search::within('foo')->where('id', '=', 1)->orWhere('title', '!==', 'Random');
    }

    public function testSearchCanBeBuiltWithValidOrWhereCondition(): void
    {
        $search = new Search();
        $search->in('foo')->where('id', '=', 1)->orWhere('title', '!=', 'Random');
        static::assertSame('id = 1 OR title != Random', $search->getRaw()['filters']);

        $search = Search::within('foo')->where('id', '=', 1)->orWhere('title', '!=', 'Random');
        static::assertSame('id = 1 OR title != Random', $search->getRaw()['filters']);
    }

    public function testSearchCannotBeBuiltWithInvalidNotCondition(): void
    {
        $search = new Search();

        static::expectException(InvalidSearchConfigurationException::class);
        $search->in('foo')->not('id', '===', 1);

        static::expectException(InvalidSearchConfigurationException::class);
        Search::within('foo')->not('id', '===', 1);
    }

    public function testSearchCanBeBuiltWithValidNotCondition(): void
    {
        $search = new Search();
        $search->in('foo')->not('id', '=', 1);
        static::assertSame('NOT id = 1', $search->getRaw()['filters']);

        $search = Search::within('foo')->not('id', '=', 1);
        static::assertSame('NOT id = 1', $search->getRaw()['filters']);
    }

    public function testSearchCanDefineDisplayedFields(): void
    {
        $search = new Search();
        $search->in('foo')->shouldRetrieve();
        static::assertSame('*', $search->getRaw()['attributesToRetrieve']);

        $search = Search::within('foo')->shouldRetrieve();
        static::assertSame('*', $search->getRaw()['attributesToRetrieve']);
    }

    public function testSearchCanSpecifyDisplayedFields(): void
    {
        $search = new Search();
        $search->in('foo')->shouldRetrieve(['id', 'title', 'tags']);
        static::assertNotEmpty($search->getRaw()['attributesToRetrieve']);
        static::assertSame('id,title,tags', $search->getRaw()['attributesToRetrieve']);

        $search = Search::within('foo')->shouldRetrieve(['id', 'title', 'tags']);
        static::assertNotEmpty($search->getRaw()['attributesToRetrieve']);
        static::assertSame('id,title,tags', $search->getRaw()['attributesToRetrieve']);
    }

    public function testSearchCanDefineHighLightedFields(): void
    {
        $search = new Search();
        $search->in('foo')->shouldHighlight('*');
        static::assertNotEmpty($search->getRaw()['attributesToHighlight']);
        static::assertSame('*', $search->getRaw()['attributesToHighlight']);

        $search = Search::within('foo')->shouldHighlight('*');
        static::assertNotEmpty($search->getRaw()['attributesToHighlight']);
        static::assertSame('*', $search->getRaw()['attributesToHighlight']);
    }

    public function testSearchCanSpecifyHighLightedFields(): void
    {
        $search = new Search();
        $search->in('foo')->shouldHighlight(['id', 'title', 'tags']);
        static::assertNotEmpty($search->getRaw()['attributesToHighlight']);
        static::assertSame('id,title,tags', $search->getRaw()['attributesToHighlight']);

        $search = Search::within('foo')->shouldHighlight(['id', 'title', 'tags']);
        static::assertNotEmpty($search->getRaw()['attributesToHighlight']);
        static::assertSame('id,title,tags', $search->getRaw()['attributesToHighlight']);
    }

    public function testSearchCanSpecifyFacetFilters(): void
    {
        $search = new Search();
        $search->in('foo')->addFacetFilter('id', '1');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame(['id:1'], $search->getRaw()['facetFilters'][0]);

        $search = Search::within('foo')->addFacetFilter('id', '1');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame(['id:1'], $search->getRaw()['facetFilters'][0]);
    }

    public function testSearchCanSpecifyOrFacetFilters(): void
    {
        $search = new Search();
        $search->in('foo')->addOrFacetFilter('id', '1', 'title', 'Foo');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame([['id:1', 'title:Foo']], $search->getRaw()['facetFilters'][0]);

        $search = Search::within('foo')->addOrFacetFilter('id', '1', 'title', 'Foo');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame([['id:1', 'title:Foo']], $search->getRaw()['facetFilters'][0]);
    }

    public function testSearchCanSpecifyAndFacetFilters(): void
    {
        $search = new Search();
        $search->in('foo')->addAndFacetFilter('id', '1', 'title', 'Foo');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame(['id:1', 'title:Foo'], $search->getRaw()['facetFilters'][0]);

        $search = Search::within('foo')->addAndFacetFilter('id', '1', 'title', 'Foo');
        static::assertNotEmpty($search->getRaw()['facetFilters']);
        static::assertSame(['id:1', 'title:Foo'], $search->getRaw()['facetFilters'][0]);
    }

    public function testSearchCanPaginate(): void
    {
        $search = new Search();
        $search->in('foo')->paginate('id', '>', 100, 20);
        static::assertNotEmpty($search->getRaw()['filters']);
        static::assertSame('id > 100', $search->getRaw()['filters']);
        static::assertSame(20, $search->getRaw()['limit']);

        $search = Search::within('foo')->paginate('id', '>', 100, 20);
        static::assertNotEmpty($search->getRaw()['filters']);
        static::assertSame('id > 100', $search->getRaw()['filters']);
        static::assertSame(20, $search->getRaw()['limit']);
    }
}
