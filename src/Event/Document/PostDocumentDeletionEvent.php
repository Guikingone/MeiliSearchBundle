<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentDeletionEvent extends Event implements DocumentEventInterface
{
    public function __construct(private readonly int $update)
    {
    }

    public function getUpdate(): int
    {
        return $this->update;
    }
}
