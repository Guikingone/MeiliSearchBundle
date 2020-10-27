<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Dump;

use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableDumpOrchestrator implements DumpOrchestratorInterface, TraceableDataCollectorInterface
{
    private const CREATED_DUMP = 'createdDump';
    private const RETRIEVED_DUMP = 'retrievedDump';

    /**
     * @var DumpOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var array<string, array>
     */
    private $data = [
        self::CREATED_DUMP => [],
        self::RETRIEVED_DUMP => [],
    ];

    public function __construct(DumpOrchestratorInterface $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): array
    {
        $dump = $this->orchestrator->create();

        $this->data[self::CREATED_DUMP][][$dump['uid']] = $dump['status'];

        return $dump;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(string $dump): array
    {
        $retrievedDump = $this->orchestrator->getStatus($dump);

        $this->data[self::RETRIEVED_DUMP][$retrievedDump['uid']] = $retrievedDump;

        return $retrievedDump;
    }

    /**
     * @return array<string, array>
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
            self::CREATED_DUMP => [],
            self::RETRIEVED_DUMP => [],
        ];
    }
}
