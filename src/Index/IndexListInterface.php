<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Closure;
use Countable;
use IteratorAggregate;
use Meilisearch\Endpoints\Indexes;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexListInterface extends Countable, IteratorAggregate
{
    public function add(Indexes $index): void;

    public function remove(string $name): void;

    public function has(string $name): bool;

    /**
     * Return a new {@see IndexListInterface} instance with the indexes filtered using `array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH)`.
     *
     * The method DOES not trigger a new request.
     *
     *
     */
    public function filter(Closure $filter): IndexListInterface;

    /**
     * @return array<string, Indexes>
     */
    public function toArray(): array;
}
