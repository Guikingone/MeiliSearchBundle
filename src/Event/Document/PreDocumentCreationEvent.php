<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use MeiliSearch\Index;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentCreationEvent extends Event
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var array<mixed,mixed>
     */
    private $document;

    public function __construct(Index $index, array $document)
    {
        $this->index = $index;
        $this->document = $document;
    }

    public function getIndex(): Index
    {
        return $this->index;
    }

    /**
     * @return array<mixed,mixed>
     */
    public function getDocument(): array
    {
        return $this->document;
    }
}
