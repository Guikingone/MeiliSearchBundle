<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class TraceableIndexSettingsOrchestrator implements IndexSettingsOrchestratorInterface
{
    /**
     * @var IndexSettingsOrchestratorInterface
     */
    private $orchestrator;

    /**
     * @var array<string,array>
     */
    private $data = [
        'updatedSettings' => [],
        'retrievedSettings' => [],
        'resetSettings' => [],
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

        $this->data['updatedSettings'][$uid][] = $updatePayload;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveSettings(string $index): array
    {
        $settings = $this->orchestrator->retrieveSettings($index);

        $this->data['retrievedSettings'][$index][] = $settings;

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function resetSettings(string $uid): void
    {
        $this->orchestrator->resetSettings($uid);

        $this->data['resetSettings'][] = $uid;
    }

    /**
     * @return array<string,array>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
