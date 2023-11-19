<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSearchEvent extends Event implements SearchEventInterface
{
    public function __construct(
        /**
         * @var array<string,mixed>
         */
        private array $configuration
    ) {
    }

    public function getSpecificConfiguration(string $key, mixed $default = null): mixed
    {
        return $this->configuration[$key] ?? $default;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }
}
