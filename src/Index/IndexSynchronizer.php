<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearchBundle\Health\HealthEntryPointInterface;
use MeiliSearchBundle\Metadata\IndexMetadata;
use MeiliSearchBundle\Metadata\IndexMetadataInterface;
use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use MeiliSearchBundle\Settings\SettingsEntryPointInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

use function array_filter;
use function array_walk;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexSynchronizer implements IndexSynchronizerInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexOrchestratorInterface $indexOrchestrator,
        private readonly IndexMetadataRegistryInterface $indexMetadataRegistry,
        private readonly HealthEntryPointInterface $healthEntryPoint,
        SettingsEntryPointInterface $settingsEntryPoint,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function createIndexes(array $indexes, ?string $prefix = null): void
    {
        if ($this->isSynchronized() && null === $prefix) {
            $this->logger->info(
                'The indexes cannot be created as the instance and the local storage are already synchronized'
            );

            return;
        }

        $this->handleMetadataIndexes($indexes, $prefix);

        $instanceIndexes = $this->indexOrchestrator->getIndexes();
        $nonPersistedIndexes = array_filter(
            $this->indexMetadataRegistry->toArray(),
            static fn (IndexMetadataInterface $indexMetadata): bool => !$instanceIndexes->has($indexMetadata->getUid())
        );

        if (empty($nonPersistedIndexes)) {
            return;
        }

        try {
            foreach ($nonPersistedIndexes as $nonPersistedIndex) {
                $this->indexOrchestrator->addIndex(
                    $nonPersistedIndex->getUid(),
                    $nonPersistedIndex->getPrimaryKey(),
                    $nonPersistedIndex->toArray()
                );
            }
        } catch (Throwable $throwable) {
            $this->logger->critical('An error occurred when creating the indexes', [
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateIndexes(array $indexes, ?string $prefix = null): void
    {
        $this->handleMetadataIndexes($indexes, $prefix, true);

        try {
            foreach ($this->indexMetadataRegistry->toArray() as $index) {
                $this->indexOrchestrator->update(
                    $index->getUid(),
                    $index->toArray()
                );
            }
        } catch (Throwable $throwable) {
            $this->logger->critical('An error occurred when updating the indexes', [
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dropIndex(string $index): void
    {
        try {
            $this->indexOrchestrator->removeIndex($index);
        } catch (Throwable $throwable) {
            $this->logger->critical('The index cannot be dropped', [
                'index' => $index,
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }

        $this->indexMetadataRegistry->remove($index);
    }

    public function isSynchronized(): bool
    {
        if (!$this->isUp()) {
            return false;
        }

        $count = $this->indexMetadataRegistry->count();

        if (0 === $count) {
            return false;
        }

        $instanceIndexes = $this->indexOrchestrator->getIndexes();

        return $count === $instanceIndexes->count();
    }

    private function isUp(): bool
    {
        return $this->healthEntryPoint->isUp();
    }

    private function handleMetadataIndexes(array $indexes, ?string $prefix = null, bool $override = false): void
    {
        array_walk($indexes, function (array $index, string $key) use ($prefix, $override): void {
            $indexName = null !== $prefix ? sprintf('%s%s', $prefix, $key) : $key;
            $indexMetadata = new IndexMetadata(
                $indexName,
                $index['async'] ?? false,
                $index['primaryKey'] ?? null,
                $index['rankingRules'] ?? [],
                $index['stopWords'] ?? [],
                $index['distinctAttribute'] ?? null,
                $index['facetedAttributes'] ?? [],
                $index['searchableAttributes'] ?? [],
                $index['displayedAttributes'] ?? [],
                $index['synonyms'] ?? []
            );

            $override ?
                $this->indexMetadataRegistry->override($indexName, $indexMetadata)
                : $this->indexMetadataRegistry->add($indexName, $indexMetadata);
        });
    }
}
