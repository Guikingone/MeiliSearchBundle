<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use Countable;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEventList implements SearchEventListInterface, Countable
{
    /**
     * @var array<int, SearchEventInterface>
     */
    private $events = [];

    /**
     * {@inheritdoc}
     */
    public function add(SearchEventInterface $event): void
    {
        $this->events[] = $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostSearchEvents(): array
    {
        return array_filter($this->events, function (SearchEventInterface $event): bool {
            return $event instanceof PostSearchEvent;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPreSearchEvents(): array
    {
        return array_filter($this->events, function (SearchEventInterface $event): bool {
            return $event instanceof PreSearchEvent;
        });
    }

    /**
     * {@inheritdoc}
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
        return count($this->events);
    }
}
