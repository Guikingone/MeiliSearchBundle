<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexEventListInterface
{
    public function add(IndexEventInterface $index): void;

    /**
     * @return array<int, IndexCreatedEvent>
     */
    public function getIndexCreatedEvents(): array;

    /**
     * @return array<int, IndexRemovedEvent>
     */
    public function getIndexRemovedEvents(): array;

    /**
     * @return array<int, IndexRetrievedEvent>
     */
    public function getIndexRetrievedEvents(): array;

    /**
     * @return array<int, PostSettingsUpdateEvent>
     */
    public function getPostSettingsUpdateEvents(): array;

    /**
     * @return array<int, PreSettingsUpdateEvent>
     */
    public function getPreSettingsUpdateEvents(): array;

    /**
     * @return array<int, IndexEventInterface>
     */
    public function getEvents(): array;
}
