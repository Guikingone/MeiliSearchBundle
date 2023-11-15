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
    public function __construct(private readonly SearchResultInterface $result)
    {
    }

    public function getResult(): SearchResultInterface
    {
        return $this->result;
    }
}
