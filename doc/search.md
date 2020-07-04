# Search

This bundle provides a [Search](../src/Search/Search.php) utils 
that allows you to build complex queries using a fluent interface. 

## Querying an index

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo');

// OR

$search = Search::within('foo');
```

## Conditions

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

## Limiting the results

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->max(10);

// OR

$search = Search::within('foo')->max(10);
```

## Defining an offset

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->offset(10);

// OR

$search = Search::within('foo')->offset(10);
```

## Limiting the retrieved attributes

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->shouldRetrieve(['id', 'title', 'tags']);

// OR

$search = Search::within('foo')->shouldRetrieve(['id', 'title', 'tags']);
```

## Defining highlighted attributes

```php
<?php

use MeiliSearchBundle\Search\Search;

$search = new Search();
$search->in('foo')->shouldHighlight(['id', 'title', 'tags']);

// OR

$search = Search::within('foo')->shouldHighlight(['id', 'title', 'tags']);
```

## Pagination

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
