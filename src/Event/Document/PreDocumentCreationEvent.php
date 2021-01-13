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
     * @var array<string, mixed>
     */
    private $document;

    /**
     * @param Indexes              $index
     * @param array<string, mixed> $document
     */
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
     * @return array<string, mixed>
     */
    public function getDocument(): array
    {
        return $this->document;
    }
}
