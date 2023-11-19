<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Result;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ResultBuilderInterface
{
    public const MODEL_KEY = 'model';

    /**
     * @param array<string,mixed> $data
     */
    public function support(array $data): bool;

    public function build(array $data, array $buildContext = []): mixed;
}
