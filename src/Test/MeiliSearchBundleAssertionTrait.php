<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Test;

use MeiliSearchBundle\Event\Index\IndexEventListInterface;
use MeiliSearchBundle\Event\SearchEventListInterface;
use MeiliSearchBundle\Test\Constraint\Index\IndexCreated;
use MeiliSearchBundle\Test\Constraint\Index\IndexRemoved;
use MeiliSearchBundle\Test\Constraint\Search;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
trait MeiliSearchBundleAssertionTrait
{
    public static function assertIndexCreatedCount(int $count, string $message = ''): void
    {
        self::assertThat(self::getIndexEventList(), new IndexCreated($count), $message);
    }

    public static function assertIndexRemovedCount(int $count, string $message = ''): void
    {
        self::assertThat(self::getIndexEventList(), new IndexRemoved($count), $message);
    }

    public static function assertSearchCount(int $count, string $message = ''): void
    {
        self::assertThat(self::getSearchEventList(), new Search($count), $message);
    }

    private static function getIndexEventList(): IndexEventListInterface
    {
        if (self::$container->has(IndexEventListInterface::class)) {
            return self::$container->get(IndexEventListInterface::class)->getEvents();
        }

        if (self::$container->has(IndexEventListInterface::class)) {
            return self::$container->get(IndexEventListInterface::class)->getEvents();
        }

        static::fail('The MeiliSearchBundle must be installed');
    }

    private static function getSearchEventList(): IndexEventListInterface
    {
        if (self::$container->has(SearchEventListInterface::class)) {
            return self::$container->get(SearchEventListInterface::class)->getEvents();
        }

        if (self::$container->has(SearchEventListInterface::class)) {
            return self::$container->get(SearchEventListInterface::class)->getEvents();
        }

        static::fail('The MeiliSearchBundle must be installed');
    }
}
