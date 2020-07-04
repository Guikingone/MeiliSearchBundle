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
     * @var array<string,mixed>
     */
    private $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getSpecificConfiguration(string $key, $default = null)
    {
        return $this->configuration[$key] ?? $default;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
