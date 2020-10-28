<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use MeiliSearchBundle\Search\SearchResultInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEvent extends Event implements SearchEventInterface
{
    /**
     * @var SearchResultInterface
     */
    private $result;

    public function __construct(SearchResultInterface $result)
    {
        $this->result = $result;
    }

    public function getResult(): SearchResultInterface
    {
        return $this->result;
    }
}
