<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface SearchEventListInterface
{
    /**
     * @return array<int, PostSearchEvent>
     */
    public function getPostSearchEvents(): array;

    /**
     * @return array<int, PreSearchEvent>
     */
    public function getPreSearchEvents(): array;

    /**
     * @return array<int, SearchEventInterface>
     */
    public function getEvents(): array;
}
