<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentCreationEvent extends Event implements DocumentEventInterface
{
    public function __construct(private readonly Indexes $index, private readonly int $update)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    public function getUpdate(): int
    {
        return $this->update;
    }
}
