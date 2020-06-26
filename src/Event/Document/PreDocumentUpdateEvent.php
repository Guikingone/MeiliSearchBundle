<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentUpdateEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private $document;

    public function __construct(array $document)
    {
        $this->document = $document;
    }

    public function getDocument(): array
    {
        return $this->document;
    }
}
