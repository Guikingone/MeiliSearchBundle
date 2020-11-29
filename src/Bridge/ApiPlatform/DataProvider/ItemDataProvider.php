<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\Bridge\Elasticsearch\Exception\IndexNotFoundException;
use ApiPlatform\Core\Bridge\Elasticsearch\Exception\NonUniqueIdentifierException;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use MeiliSearchBundle\Bridge\ApiPlatform\Api\IdentifierExtractor;
use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $denormalizer;
    private $documentEntryPoint;
    private $identifierExtractor;
    private $resourceMetadataFactory;

    public function __construct(
        DenormalizerInterface $denormalizer,
        DocumentEntryPointInterface $documentEntryPoint,
        IdentifierExtractor $identifierExtractor,
        ResourceMetadataFactoryInterface $resourceMetadataFactory
    ) {
        $this->denormalizer = $denormalizer;
        $this->documentEntryPoint = $documentEntryPoint;
        $this->identifierExtractor = $identifierExtractor;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        try {
            $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
            if (false === $resourceMetadata->getItemOperationAttribute($operationName, 'meilisearch', true, true)) {
                return false;
            }
        } catch (ResourceClassNotFoundException $e) {
            return false;
        }

        try {
            $this->documentMetadataFactory->create($resourceClass);
        } catch (IndexNotFoundException $e) {
            return false;
        }

        try {
            $this->identifierExtractor->getIdentifierFromResourceClass($resourceClass);
        } catch (NonUniqueIdentifierException $e) {
            return false;
        }

        return true;
    }
}
