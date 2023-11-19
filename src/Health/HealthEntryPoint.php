<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Health;

use Meilisearch\Client;

use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class HealthEntryPoint implements HealthEntryPointInterface
{
    public function __construct(private readonly Client $client)
    {
    }

    public function isUp(): bool
    {
        $stats = $this->client->stats();

        return 0 !== count($stats['indexes']);
    }
}
