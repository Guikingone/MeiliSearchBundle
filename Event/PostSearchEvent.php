<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use MeiliSearchBundle\Client\Search;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEvent extends Event
{
    /**
     * @var Search
     */
    private $result;

    public function __construct(Search $result)
    {
        $this->result = $result;
    }

    public function getResult(): Search
    {
        return $this->result;
    }
}
