<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Settings;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SettingsEntryPoint implements SettingsEntryPointInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly IndexOrchestratorInterface $indexOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings(string $index): Settings
    {
        try {
            $index = $this->indexOrchestrator->getIndex($index);

            return Settings::create($index->getSettings());
        } catch (Throwable $throwable) {
            $this->logger->error('The settings cannot be retrieved', [
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }
}
