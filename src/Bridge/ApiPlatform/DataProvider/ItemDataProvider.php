<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        // TODO: Implement getItem() method.
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        // TODO: Implement supports() method.
    }
}
