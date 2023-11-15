<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Attribute;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ConfigurationAttributeInterface
{
    public function __construct(string $index, string|null $primaryKey = null, bool $model = false);
}
