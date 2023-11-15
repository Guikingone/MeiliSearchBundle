<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

use MeiliSearchBundle\Dump\DumpOrchestratorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentMigrationOrchestrator implements DocumentMigrationOrchestratorInterface
{
    private readonly LoggerInterface $logger;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        private readonly DocumentEntryPointInterface $documentEntryPoint,
        private readonly DumpOrchestratorInterface $dumpOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(
        string $oldIndexUid,
        string $newIndexUid,
        bool $removeOldIndexDocuments = false,
        array $options = []
    ): void {
        $oldIndexDocuments = $this->documentEntryPoint->getDocuments($oldIndexUid, $options);
        if (empty($oldIndexDocuments)) {
            $this->logger->info(
                sprintf('The documents from "%s" cannot be migrated as the document list is empty', $oldIndexUid)
            );

            return;
        }

        $this->dumpOrchestrator->create();

        try {
            $this->documentEntryPoint->addDocuments($newIndexUid, $oldIndexDocuments);
        } catch (Throwable $throwable) {
            $this->logger->critical(
                'The documents cannot be migrated, a dump has been created before trying to add the new documents',
                [
                    'error' => $throwable->getMessage(),
                    'index' => $newIndexUid,
                ]
            );

            throw $throwable;
        }

        $this->logger->info('The documents have been migrated', [
            'index' => $oldIndexUid,
            'nextIndex' => $newIndexUid,
        ]);

        if ($removeOldIndexDocuments) {
            $this->documentEntryPoint->removeDocuments($oldIndexUid);
        }
    }
}
