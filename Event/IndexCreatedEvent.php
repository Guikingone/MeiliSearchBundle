<?php

namespace MeiliBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedEvent extends Event
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
