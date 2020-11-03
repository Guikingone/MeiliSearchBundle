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
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IndexOrchestratorInterface $indexOrchestrator,
        ?LoggerInterface $logger = null
    ) {
        $this->indexOrchestrator = $indexOrchestrator;
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
