<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use MeiliSearch\Index;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexCreatedEvent extends Event
{
    /**
     * @var <string, mixed>
     */
    private $config;

    /**
     * @var Index
     */
    private $index;

    public function __construct(array $config, Index $index)
    {
        $this->config = $config;
        $this->index = $index;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getIndex(): Index
    {
        return $this->index;
    }
}
