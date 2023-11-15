<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Synonyms;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreResetSynonymsEvent extends Event
{
    public function __construct(private readonly Indexes $index)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }
}
