<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;
use function array_walk;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexOrchestrator implements IndexOrchestratorInterface, TraceableDataCollectorInterface
{
    private const CREATED_INDEXES = 'createdIndexes';
    private const FETCHED_INDEXES = 'fetchedIndexes';
    private const DELETED_INDEXES = 'deletedIndexes';

    private const UID_DATA_KEY = 'uid';
    private const PRIMARY_KEY_LOG_KEY = 'primaryKey';
    private const INDEX_CREATION_DATE_KEY = 'createdAt';
    private const INDEX_UPDATE_DATE_KEY = 'updateddAt';

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
     * @return array<string, Indexes>
     */
    public function getIndexes(): array
    {
        $indexes = $this->orchestrator->getIndexes();

        array_walk($indexes, function (Indexes $index): void {
            $informations = $index->show();

            $this->data[self::FETCHED_INDEXES][] = [
                self::UID_DATA_KEY => $informations['uid'],
                self::PRIMARY_KEY_LOG_KEY => $informations['primaryKey'],
                self::INDEX_CREATION_DATE_KEY => $informations['createdAt'],
                self::INDEX_UPDATE_DATE_KEY => $informations['updatedAt'],
            ];
        });

        return $indexes;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex(string $uid): Indexes
    {
        $index = $this->orchestrator->getIndex($uid);

        $informations = $index->show();
        $this->data[self::FETCHED_INDEXES][] = [
            self::UID_DATA_KEY => $informations['uid'],
            self::PRIMARY_KEY_LOG_KEY => $informations['primaryKey'],
            self::INDEX_CREATION_DATE_KEY => $informations['createdAt'],
            self::INDEX_UPDATE_DATE_KEY => $informations['updatedAt'],
        ];

        return $index;
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
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [
            self::CREATED_INDEXES => [],
            self::FETCHED_INDEXES => [],
            self::DELETED_INDEXES => [],
        ];
    }
}
