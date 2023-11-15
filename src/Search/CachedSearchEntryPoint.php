<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Search;

use MeiliSearchBundle\Cache\SearchResultCacheOrchestratorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

use function sprintf;
use function strtolower;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class CachedSearchEntryPoint implements SearchEntryPointInterface
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly SearchResultCacheOrchestratorInterface $cacheOrchestrator,
        private readonly SearchEntryPointInterface $searchEntryPoint,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $index, string $query, array $options = []): SearchResultInterface
    {
        $cacheItemKey = sprintf('%s_%s', $index, strtolower($query));

        try {
            return $this->cacheOrchestrator->get($cacheItemKey);
        } catch (Throwable) {
            $this->logger->error(
                'An undefined SearchResult has been queried, a fallback to the MeiliSearch API has been made'
            );

            $result = $this->searchEntryPoint->search($index, $query, $options);
            $this->cacheOrchestrator->add($cacheItemKey, $result);

            return $result;
        }
    }
}
