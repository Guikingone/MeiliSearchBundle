<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Document;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreDocumentRetrievedEvent extends Event
{
    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var string|int
     */
    private $id;

    /**
     * @param Indexes    $index
     * @param int|string $id
     */
    public function __construct(Indexes $index, $id)
    {
        $this->index = $index;
        $this->id = $id;
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
