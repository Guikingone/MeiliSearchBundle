<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private $config;

    /**
     * @var Indexes
     */
    private $index;

    /**
     * @param array<string,mixed> $config
     * @param Indexes             $index
     */
    public function __construct(array $config, Indexes $index)
    {
        $this->config = $config;
        $this->index = $index;
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
