<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentDeletionEvent extends Event
{
    /**
     * @var array<string,mixed>
     */
    private $index;

    public function __construct(array $index)
    {
        $this->index = $index;
    }

    /**
     * @return array<string,mixed>
     */
    public function getIndex(): array
    {
        return $this->index;
    }
}