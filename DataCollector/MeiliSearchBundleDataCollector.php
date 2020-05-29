<?php

namespace MeiliBundle\DataCollector;

use MeiliBundle\Client\TraceableClient;
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
        $this->data['indexes'] = $this->client->getIndexes();
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
}
