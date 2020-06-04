<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

use MeiliSearch\Index;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexOrchestrator implements IndexOrchestratorInterface
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var string[]
     */
    private $createdIndexes = [];

    /**
     * @var string[]
     */
    private $deletedIndexes = [];

    /**
     * @var string[]
     */
    private $fetchedIndexes = [];

    public function __construct(IndexOrchestratorInterface $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $uid, ?string $primaryKey = null): void
    {
        $this->orchestrator->addIndex($uid, $primaryKey);

        $this->createdIndexes[] = ['uid' => $uid, 'primaryKey' => $primaryKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        return $this->orchestrator->getIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Index
    {
        $this->fetchedIndexes[] = $uid;

        return $this->orchestrator->getIndex($uid);
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndexes(): void
    {
        $this->orchestrator->removeIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function removeIndex(string $uid): void
    {
        $this->orchestrator->removeIndex($uid);

        $this->deletedIndexes[] = ['uid' => $uid];
    }

    public function getCreatedIndexes(): array
    {
        return $this->createdIndexes;
    }

    public function getFetchedIndexes(): array
    {
        return $this->fetchedIndexes;
    }

    public function getDeletedIndexes(): array
    {
        return $this->deletedIndexes;
    }
}
