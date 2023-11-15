<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentRetrievedEvent extends Event implements DocumentEventInterface
{
    /**
     * @param array<string,mixed> $document
     */
    public function __construct(private readonly Indexes $index, private readonly array $document)
    {
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDocument(): array
    {
        return $this->document;
    }
}
