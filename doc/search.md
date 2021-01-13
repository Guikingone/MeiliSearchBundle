# Search

Chapters:

- [Tools](search.md#Tools)
- [Scoped indexes](search.md#Scoped indexes)

## Tools

This bundle provides a [Search](../src/Search/Search.php) utils 
that allows you to build complex queries using a fluent interface. 

## Building a search

### Querying an index

```php
<?php

declare(strict_types=1);

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo');

// OR

$search = Search::within('foo');
```

**Note**: Keep in mind that using `Search::within('foo')` is just a shortcut for the first syntax.

### Specifying the query

```php
<?php

declare(strict_types=1);

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo');
$search->query('bar');

// OR

$search = Search::on('foo', 'bar');
```

**Note**: Keep in mind that using `Search::on('foo', 'bar')` is just a shortcut for the first syntax.

### Conditions

Conditions (or filters in MeiliSearch) can be hard to handle, 
thanks to `Search`, you can easily combine almost every filter that you may need.

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->where('id', '=', 1);

// OR

$search = Search::within('foo')->where('id', '=', 1);
```

Want to filter on multiple conditions?

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random');

// OR

$search = Search::within('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random');
```

Want to filter on X OR Y?

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->where('id', '=', 1)->orWhere('title', '!=', 'Random');

// OR

$search = Search::within('foo')->where('id', '=', 1)->orWhere('title', '!=', 'Random');
```

Want to filter on a negative condition?

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->not('id', '=', 1);

// OR

$search = Search::within('foo')->not('id', '=', 1);
```

Want to filter on an "isolated" negative condition?

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->where('id', '>', 1)->andNot('id', '=', 5);

// OR

$search = Search::within('foo')->where('id', '>', 1)->andNot('id', '=', 5);

// Both will result on id > 1 AND (NOT id = 5)
```

**Note**: Keep in mind that `andNot` cannot be used without an existing `where` condition!

**Note**: Every `*where*` method define a third (fourth on `where`) argument called `$isolated` which allow to use `()` to isolate the condition.

Keep in mind that filters can be chained: 

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random')->orWhere('title', '=', 'Hello World');

// OR

$search = Search::within('foo')->where('id', '=', 1)->andWhere('title', '!=', 'Random')->orWhere('title', '=', 'Hello World');

// Both will produce id = 1 AND title != 'Random' OR title = 'Hello World'
```

### Limiting the results

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->max(10);

// OR

$search = Search::within('foo')->max(10);
```

### Defining an offset

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->offset(10);

// OR

$search = Search::within('foo')->offset(10);
```

### Limiting the retrieved attributes

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->shouldRetrieve(['id', 'title', 'tags']);

// OR

$search = Search::within('foo')->shouldRetrieve(['id', 'title', 'tags']);
```

### Defining highlighted attributes

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->shouldHighlight(['id', 'title', 'tags']);

// OR

$search = Search::within('foo')->shouldHighlight(['id', 'title', 'tags']);
```

### Pagination

Paginating hits on MeiliSearch can be hard (as not supported for now), 
this bundle provides a different approach based on "cursor pagination". 

The idea behind this approach is to limit the results based on the id (or primary key)
and set a limit of results.

**Note**: Keep in mind that this approach only works if the id/primary key is an integer.

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->paginate('id', '>', 100, 20);

// OR

$search = Search::within('foo')->paginate('id', '>', 100, 20);

// Both will generate: id > 100 LIMIT 20
```

Once the query has been made in MeiliSearch, 
the [SearchResult](../src/Search/SearchResult.php) will receive every hits, 
by default, the latest id is stored in `Result::getLastIdentifier()`,
when searching the next set of data, just use the same approach as the first query.

**Note**: Keep in mind that the primary key must be `id` in order to retrieve value.

```php
<?php

use MeiliSearchBundle\Search\Search;

// Old search

$search = new Search();
$search->in('foo')->paginate('id', '>', $result->getLastIdentifier(), 20);

// OR

$search = Search::within('foo')->paginate('id', '>', $result->getLastIdentifier(), 20);
```

### Bonus: Building a search using [ExpressionLanguage](https://symfony.com/doc/current/components/expression_language.html)

This bundle provides a custom `ExpressionLanguage` that brings a shortcut to building searches:

```php
<?php

use MeiliSearchBundle\ExpressionLanguage\SearchExpressionLanguage;

$expressionLanguage = new SearchExpressionLanguage();
$search = $expressionLanguage->evaluate('search("foo", "bar", "title > 2", 2)');

// Once defined, you can update the Search object via the defined methods
// Ex:

$search->andWhere('title', '>=', 10);
```

**Note**: 

This approach only supports the following building parts of a search:

- The index
- The query
- The filters (only the ones defined by `where`)
- The limit (defined by `max`)

## Usage

// TODO

## Scoped indexes

_Since **0.2**_

This bundle provides a feature called "Scoped Indexes", this feature allows you to search on X indexes
at the same time.

### Usage

To use this feature, first, you must define indexes in the configuration file then use the `scoped_indexes` key:

```yaml
meili_search:
  # ...
  scoped_indexes:
    admin: ['admin_posts', 'tags']
```

Once defined, a `ScopedEntryPoint` is available:

```php
<?php

declare(strict_types=1);

use MeiliSearchBundle\Search\ScopedSearchEntryPoint;
use Symfony\Component\HttpFoundation\Response;

final class FooController
{
    public function __invoke(ScopedSearchEntryPoint $entryPoint): Response
    {
        $result = $entryPoint->search('admin', 'bar');
        
        // ...
    }
}
```

The only requirements here is to use the keys (in this example `admin`), once the search is triggered,
the `ScopedEntryPoint` will try to find an occurrence in both `admin_posts` and `tags` indexes.

_PS: If no occurrence is found, an exception is thrown._
