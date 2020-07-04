<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\ConfigurationAnnotationInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ReaderInterface
{
    public function __construct(AnnotationReader $reader);

    public function getConfiguration(object $object): ConfigurationAnnotationInterface;
}
