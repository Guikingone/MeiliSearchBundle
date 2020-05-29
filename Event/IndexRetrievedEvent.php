<?php

namespace MeiliSearchBundle\Event;

use MeiliSearch\Index;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRetrievedEvent extends Event
{
    /**
     * @var Index
     */
    private $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function getIndex(): Index
    {
        return $this->index;
    }
}
