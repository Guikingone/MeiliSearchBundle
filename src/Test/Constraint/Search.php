<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Test\Constraint;

use MeiliSearchBundle\Event\SearchEventListInterface;
use PHPUnit\Framework\Constraint\Constraint;

use function count;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Search extends Constraint
{
    public function __construct(
        private int $expectedCount
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return sprintf(
            '%s search%s %s been made',
            $this->expectedCount,
            $this->expectedCount > 1 ? 'es' : '',
            $this->expectedCount > 1 ? 'have' : 'has'
        );
    }

    /**
     * @param SearchEventListInterface $eventsList
     *
     * {@inheritdoc}
     */
    protected function matches($eventsList): bool
    {
        return $this->expectedCount === count($eventsList->getPostSearchEvents());
    }
}
