<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentCreationEvent extends Event
{
    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var array<mixed,mixed>
     */
    private $document;

    public function __construct(Indexes $index, array $document)
    {
        $this->index = $index;
        $this->document = $document;
    }

    public function getIndex(): Indexes
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
