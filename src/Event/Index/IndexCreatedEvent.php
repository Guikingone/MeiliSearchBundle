<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedEvent extends Event implements IndexEventInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function __construct(private readonly array $config, private readonly Indexes $index)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }
}
