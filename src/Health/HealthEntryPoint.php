<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Health;

use MeiliSearch\Client;
use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class HealthEntryPoint implements HealthEntryPointInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isUp(): bool
    {
        $stats = $this->client->stats();

        return 0 !== count($stats['indexes']);
    }
}
