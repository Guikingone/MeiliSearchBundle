<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreUpdateSynonymsEvent extends Event
{
    /**
     * @param array<string,array> $synonyms
     */
    public function __construct(private readonly Indexes $index, private readonly array $synonyms)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    /**
     * @return array<string,array>
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
    }
}
