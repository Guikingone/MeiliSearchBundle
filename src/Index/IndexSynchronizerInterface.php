<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearchBundle\Metadata\IndexMetadataRegistryInterface;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexSynchronizerInterface
{
    /**
     * @param array<string, array> $indexes
     * @param string|null          $prefix
     *
     * @throws Throwable
     */
    public function createIndexes(array $indexes, ?string $prefix = null): void;

    /**
     * Override every indexes stored in the {@see IndexMetadataRegistryInterface} then update it via {@see IndexOrchestratorInterface::update()}.
     *
     * @param array<string, array> $indexes
     * @param string|null          $prefix
     *
     * @throws Throwable
     */
    public function updateIndexes(array $indexes, ?string $prefix = null): void;

    /**
     * This method allows to drop every indexes stored in the MeiliSearch instance and locally.
     *
     * If an exception is thrown during the MeiliSearch instance operation, the local indexes are not dropped.
     *
     * @throws Throwable
     */
    public function dropIndexes(): void;

    /**
     * This method allows to drop an index stored in the MeiliSearch instance and locally.
     *
     * If an exception is thrown during the MeiliSearch instance operation, the local index is not dropped.
     *
     * @param string $index
     *
     * @throws Throwable
     */
    public function dropIndex(string $index): void;

    /**
     * Determine if the locally stored indexes are synchronized with the ones stored in the MeiliSearch instance.
     *
     * If the instance does not contains indexes, false is returned.
     *
     * If no indexes are stored locally, false is returned.
     *
     * If the locally stored indexes are not in the same amount as the ones in the MeiliSearch instance, false is returned.
     */
    public function isSynchronized(): bool;
}
