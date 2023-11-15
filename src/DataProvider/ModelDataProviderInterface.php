<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DataProvider;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ModelDataProviderInterface
{
    /**
     * If a string is returned, it MUST be the FQCN of the DTO/Value object used to denormalize the document when returned.
     */
    public function getModel(): string;
}
