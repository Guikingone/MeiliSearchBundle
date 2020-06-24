<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use MeiliSearchBundle\Client\SearchInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEvent extends Event
{
    /**
     * @var SearchInterface
     */
    private $result;

    public function __construct(SearchInterface $result)
    {
        $this->result = $result;
    }

    public function getResult(): SearchInterface
    {
        return $this->result;
    }
}
