<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader;

use ReflectionException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentReaderInterface extends ReaderInterface
{
    /**
     *
     *
     * @throws ReflectionException
     */
    public function isDocument(object $object): bool;
}
