<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader;

use MeiliSearchBundle\Bridge\Doctrine\Attribute\ConfigurationAttributeInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ReaderInterface
{
    public function getConfiguration(object $object): ConfigurationAttributeInterface;
}
