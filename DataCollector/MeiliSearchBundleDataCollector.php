<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Client\TraceableClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollector extends DataCollector implements LateDataCollectorInterface
{
    private $client;

    public function __construct(TraceableClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $this->data['system_info'] = $this->client->getSystemInformations();
        $this->data['indexes'] = $this->client->getIndexes();
        $this->data['queries'] = $this->client->getQueries();
        $this->data['created_indexes'] = $this->client->getCreatedIndexes();
        $this->data['deleted_indexes'] = $this->client->getDeletedIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'meili';
    }

    public function getSystemInformations(): array
    {
        return $this->data['system_info'];
    }

    public function getIndexes(): array
    {
        return $this->data['indexes'];
    }

    public function getCreatedIndexes(): array
    {
        return $this->data['created_indexes'];
    }

    public function getDeletedIndexes(): array
    {
        return $this->data['deleted_indexes'];
    }

    public function getQueriesCount(): int
    {
        return \count($this->client->getQueries());
    }
}
