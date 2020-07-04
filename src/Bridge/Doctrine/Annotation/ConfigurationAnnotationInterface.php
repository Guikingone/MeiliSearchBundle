<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Annotation;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ConfigurationAnnotationInterface
{
    /**
     * @param array<string,mixed> $configuration
     */
    public function __construct(array $configuration = []);
}
