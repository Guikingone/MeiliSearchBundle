<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataCollector;

use MeiliSearchBundle\Event\SearchEventListInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Throwable;

use function count;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleDataCollector extends DataCollector implements LateDataCollectorInterface
{
    public const NAME = 'meilisearch';

    private const QUERIES = 'queries';

    public function __construct(private readonly SearchEventListInterface $searchEventList)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, Throwable $exception = null): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect(): void
    {
        $this->data[self::QUERIES] = [
            'count' => count($this->searchEventList->getPostSearchEvents()),
            'searches' => $this->searchEventList->getPostSearchEvents(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data[self::QUERIES] = [
            'count' => 0,
            'searches' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getSearches(): array
    {
        return $this->data[self::QUERIES];
    }
}
