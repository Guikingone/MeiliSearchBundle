<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearch\Endpoints\Indexes;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexOrchestrator implements IndexOrchestratorInterface
{
    private const CREATED_INDEXES = 'createdIndexes';
    private const FETCHED_INDEXES = 'fetchedIndexes';
    private const DELETED_INDEXES = 'deletedIndexes';
    private const UID_DATA_KEY = 'uid';
    private const PRIMARY_KEY_LOG_KEY = 'primaryKey';

    /**
     * @var IndexOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var array<string,array>
     */
    private $data = [
        self::CREATED_INDEXES => [],
        self::FETCHED_INDEXES => [],
        self::DELETED_INDEXES => [],
    ];

    public function __construct(IndexOrchestratorInterface $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function addIndex(string $uid, ?string $primaryKey = null, array $config = []): void
    {
        $this->orchestrator->addIndex($uid, $primaryKey, $config);

        $this->data[self::CREATED_INDEXES][] = [
            self::UID_DATA_KEY => $uid,
            self::PRIMARY_KEY_LOG_KEY => $primaryKey,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexes(): array
    {
        $indexes = $this->orchestrator->getIndexes();

        foreach ($indexes as $index) {
            $this->data[self::FETCHED_INDEXES][] = [self::UID_DATA_KEY => $index['uid']];
        }

        return $indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Indexes
    {
        $this->data[self::FETCHED_INDEXES][] = [self::UID_DATA_KEY => $uid];

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

        $this->data[self::DELETED_INDEXES][] = [self::UID_DATA_KEY => $uid];
    }

    /**
     * @return array<int,array>
     */
    public function getCreatedIndexes(): array
    {
        return $this->data[self::CREATED_INDEXES];
    }

    /**
     * @return array<int,array>
     */
    public function getFetchedIndexes(): array
    {
        return $this->data[self::FETCHED_INDEXES];
    }

    /**
     * @return array<int,array>
     */
    public function getDeletedIndexes(): array
    {
        return $this->data[self::DELETED_INDEXES];
    }
}
