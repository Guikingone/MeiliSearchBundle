<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Countable;

use function array_filter;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexEventList implements IndexEventListInterface, Countable
{
    /**
     * @var array<int, IndexEventInterface>
     */
    private array $events = [];

    public function add(IndexEventInterface $index): void
    {
        $this->events[] = $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexCreatedEvents(): array
    {
        return array_filter(
            $this->events,
            static fn (IndexEventInterface $index): bool => $index instanceof IndexCreatedEvent
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexRemovedEvents(): array
    {
        return array_filter(
            $this->events,
            static fn (IndexEventInterface $index): bool => $index instanceof IndexRemovedEvent
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexRetrievedEvents(): array
    {
        return array_filter(
            $this->events,
            static fn (IndexEventInterface $index): bool => $index instanceof IndexRetrievedEvent
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPostSettingsUpdateEvents(): array
    {
        return array_filter(
            $this->events,
            static fn (IndexEventInterface $index): bool => $index instanceof PostSettingsUpdateEvent
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPreSettingsUpdateEvents(): array
    {
        return array_filter(
            $this->events,
            static fn (IndexEventInterface $index): bool => $index instanceof PreSettingsUpdateEvent
        );
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
