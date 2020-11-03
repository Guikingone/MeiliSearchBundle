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
     * @param string $oldIndexUid
     * @param string $newIndexUid
     * @param bool   $removeOldIndexDocuments Determine if the documents must be removed on the old index (once the migration is done).
     *
     * @throws Throwable
     */
    public function migrate(string $oldIndexUid, string $newIndexUid, bool $removeOldIndexDocuments = false): void;
}
