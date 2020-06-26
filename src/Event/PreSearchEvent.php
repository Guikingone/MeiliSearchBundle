<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSearchEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
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
