<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentDeletionEvent extends Event
{
    /**
     * @var int
     */
    private $update;

    public function __construct(int $update)
    {
        $this->update = $update;
    }

    public function getUpdate(): int
    {
        return $this->update;
    }
}
