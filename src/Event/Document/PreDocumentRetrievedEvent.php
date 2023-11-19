<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentRetrievedEvent extends Event
{
    /**
     * @param int|string $id
     */
    public function __construct(private readonly Indexes $index, private $id)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    /**
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }
}
