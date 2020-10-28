# Testing

This bundle provides a set of shortcuts to help during tests.

## DataCollector

// TODO

## Assertions

A set of assertions is available thanks to the [MeiliSearchBundleAssertionTrait](../src/Test/MeiliSearchBundleAssertionTrait.php)
during functional tests:

- [IndexCreated](../src/Test/Constraint/Index/IndexCreated.php) | Allow to test the number of index created:

```php
<?php

use PHPUnit\Framework\TestCase;
use MeiliSearchBundle\Test\MeiliSearchBundleAssertionTrait;

final class Test extends TestCase
{
    use MeiliSearchBundleAssertionTrait;

    public function testHomepage(): void
    {
        // ...
        
        static::assertIndexCreatedCount(1);
    }
}
```

- [IndexRemoved](../src/Test/Constraint/Index/IndexRemoved.php) | Allow to test the number of index removed:

```php
<?php

use PHPUnit\Framework\TestCase;
use MeiliSearchBundle\Test\MeiliSearchBundleAssertionTrait;

final class Test extends TestCase
{
    use MeiliSearchBundleAssertionTrait;

    public function testHomepage(): void
    {
        // ...
        
        static::assertIndexRemovedCount(1);
    }
}
```

- [Search](../src/Test/Constraint/Search.php) | Allow to test the number of search performed:

```php
<?php

use PHPUnit\Framework\TestCase;
use MeiliSearchBundle\Test\MeiliSearchBundleAssertionTrait;

final class Test extends TestCase
{
    use MeiliSearchBundleAssertionTrait;

    public function testHomepage(): void
    {
        // ...
        
        static::assertSearchCount(1);
    }
}
```
