<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSearchEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
