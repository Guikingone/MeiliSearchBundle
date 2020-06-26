<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InstanceProbe implements InstanceProbeInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemInformations(): array
    {
        return $this->client->prettySysInfo();
    }
}
