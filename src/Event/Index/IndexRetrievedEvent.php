<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRetrievedEvent extends Event implements IndexEventInterface
{
    public function __construct(private readonly Indexes $index)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }
}
