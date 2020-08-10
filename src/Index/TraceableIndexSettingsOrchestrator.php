<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use MeiliSearchBundle\DataCollector\TraceableDataCollectorInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexSettingsOrchestrator implements IndexSettingsOrchestratorInterface, TraceableDataCollectorInterface
{
    private const UPDATED_SETTINGS_KEY = 'updatedSettings';
    private const RETRIEVED_SETTINGS_KEY = 'retrievedSettings';
    private const RESET_SETTINGS_KEY = 'resetSettings';

    /**
     * @var IndexSettingsOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var array<string,array>
     */
    private $data = [
        self::UPDATED_SETTINGS_KEY => [],
        self::RETRIEVED_SETTINGS_KEY => [],
        self::RESET_SETTINGS_KEY => [],
    ];

    public function __construct(IndexSettingsOrchestratorInterface $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(string $uid, array $updatePayload): void
    {
        $this->orchestrator->updateSettings($uid, $updatePayload);

        $this->data[self::UPDATED_SETTINGS_KEY][$uid][] = $updatePayload;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveSettings(string $index): array
    {
        $settings = $this->orchestrator->retrieveSettings($index);

        $this->data[self::RETRIEVED_SETTINGS_KEY][$index][] = $settings;

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function resetSettings(string $uid): void
    {
        $this->orchestrator->resetSettings($uid);

        $this->data[self::RESET_SETTINGS_KEY][] = $uid;
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
            self::UPDATED_SETTINGS_KEY => [],
            self::RETRIEVED_SETTINGS_KEY => [],
            self::RESET_SETTINGS_KEY => [],
        ];
    }
}
