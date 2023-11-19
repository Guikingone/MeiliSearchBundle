<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use ArrayIterator;
use Closure;
use Meilisearch\Endpoints\Indexes;

use function array_filter;
use function array_key_exists;
use function array_walk;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexList implements IndexListInterface
{
    private array $indexes = [];

    public function __construct(array $indexes = [])
    {
        array_walk($indexes, function (Indexes $index): void {
            $this->add($index);
        });
    }

    public function add(Indexes $index): void
    {
        $this->indexes[$index->getUid()] = $index;
    }

    public function remove(string $name): void
    {
        if (!$this->has($name)) {
            return;
        }

        unset($this->indexes[$name]);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->indexes);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Closure $filter): IndexListInterface
    {
        return new self(array_filter($this->indexes, $filter, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->indexes);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->indexes);
    }
}
