<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader;

use ReflectionException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface DocumentReaderInterface extends ReaderInterface
{
    /**
     * @param object $object
     *
     * @return bool
     *
     * @throws ReflectionException
     */
    public function isDocument(object $object): bool;
}
