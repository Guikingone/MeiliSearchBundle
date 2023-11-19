<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentMigrationOrchestratorInterface
{
    /**
     * Migrate the documents between two indexes.
     *
     * @throws Throwable
     * @param bool $removeOldIndexDocuments Determine if the documents must be removed on the old index (once the migration is done).
     *
     */
    public function migrate(string $oldIndexUid, string $newIndexUid, bool $removeOldIndexDocuments = false): void;
}
