<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Index;

use Meilisearch\Client;
use MeiliSearchBundle\Event\Index\PostSettingsUpdateEvent;
use MeiliSearchBundle\Event\Index\PreSettingsUpdateEvent;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function in_array;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexSettingsOrchestrator implements IndexSettingsOrchestratorInterface
{
    private const INDEX_LOG_KEY = 'index';

    private const ERROR_LOG_KEY = 'error';

    private const UPDATE_KEY = 'updateId';

    private const ALLOWED_SETTINGS_KEY = [
        'rankingRules',
        'stopWords',
        'synonyms',
        'attributesForFaceting',
        'distinctAttribute',
        'searchableAttributes',
        'displayedAttributes',
    ];

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly Client $client,
        ?LoggerInterface $logger = null,
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveSettings(string $uid): array
    {
        try {
            $index = $this->client->getIndex($uid);

            return $index->getSettings();
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when fetching the settings', [
                self::INDEX_LOG_KEY => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
            ]);
            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(string $uid, array $updatePayload): void
    {
        if (empty($updatePayload)) {
            return;
        }

        try {
            foreach (array_keys($updatePayload) as $key) {
                if (!in_array($key, self::ALLOWED_SETTINGS_KEY)) {
                    throw new InvalidArgumentException(sprintf('The following key "%s" is not allowed', $key));
                }
            }

            $index = $this->client->getIndex($uid);

            $this->dispatch(new PreSettingsUpdateEvent($index, $updatePayload));
            $update = $index->updateSettings($updatePayload);
            $this->dispatch(new PostSettingsUpdateEvent($index, $update[self::UPDATE_KEY]));
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when updating the settings', [
                self::INDEX_LOG_KEY => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
            ]);
            throw $throwable;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetSettings(string $uid): void
    {
        try {
            $index = $this->client->getIndex($uid);

            $index->resetSettings();
        } catch (Throwable $throwable) {
            $this->logger->error('An error occurred when trying to reset the settings', [
                self::INDEX_LOG_KEY => $uid,
                self::ERROR_LOG_KEY => $throwable->getMessage(),
            ]);
            throw $throwable;
        }
    }

    private function dispatch(Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event);
    }
}
