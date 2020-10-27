<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Countable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexEventList implements Countable
{
    /**
     * @var array<int, IndexEventInterface>
     */
    private $events = [];

    /**
     * @return array<int, IndexEventInterface>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->events);
    }
}
