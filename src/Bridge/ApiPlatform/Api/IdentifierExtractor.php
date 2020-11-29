<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\ApiPlatform\Api;

use ApiPlatform\Core\Api\IdentifiersExtractorInterface;
use ApiPlatform\Core\Bridge\Elasticsearch\Api\IdentifierExtractorInterface;
use ApiPlatform\Core\Bridge\Elasticsearch\Exception\NonUniqueIdentifierException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IdentifierExtractor implements IdentifierExtractorInterface
{
    private $identifiersExtractor;

    public function __construct(IdentifiersExtractorInterface $identifiersExtractor)
    {
        $this->identifiersExtractor = $identifiersExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFromResourceClass(string $resourceClass): string
    {
        // TODO: Implement getIdentifierFromResourceClass() method.
    }
}
