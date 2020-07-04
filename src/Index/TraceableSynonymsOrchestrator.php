<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableSynonymsOrchestrator implements SynonymsOrchestratorInterface
{
    private const INDEX = 'index';
    private const SYNONYMS = 'synonyms';

    /**
     * @var SynonymsOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array<string,array>
     */
    private $data = [
        'fetchedSynonyms' => [],
        'updatedSynonyms' => [],
    ];

    public function __construct(
        SynonymsOrchestratorInterface $orchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->orchestrator = $orchestrator;
        $this->logger = $logger ?: new NullLogger();
    }

    public function getSynonyms(string $uid): array
    {
        $synonyms = $this->orchestrator->getSynonyms($uid);

        $this->logger->info('The following synonyms have been found', [
            self::INDEX => $uid,
            self::SYNONYMS => $synonyms,
        ]);

        $this->data['fetchedSynonyms'][$uid][] = $synonyms;

        return $synonyms;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSynonyms(string $uid, array $synonyms): void
    {
        $this->orchestrator->updateSynonyms($uid, $synonyms);

        $this->data['updatedSynonyms'][$uid][] = $synonyms;

        $this->logger->info('The following synonyms have been updated', [
            self::INDEX => $uid,
            self::SYNONYMS => $synonyms,
        ]);
    }

    public function resetSynonyms(string $uid): void
    {
        $this->orchestrator->resetSynonyms($uid);

        $this->logger->info('The following index has reset its synonyms', [
            self::INDEX => $uid,
        ]);
    }

    /**
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
