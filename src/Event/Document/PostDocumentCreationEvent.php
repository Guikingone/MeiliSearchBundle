<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use MeiliSearch\Index;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostDocumentCreationEvent extends Event
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var int
     */
    private $update;

    public function __construct(Index $index, int $update)
    {
        $this->index = $index;
        $this->update = $update;
    }

    public function getIndex(): Index
    {
        return $this->index;
    }

    public function getUpdate(): int
    {
        return $this->update;
    }
}
