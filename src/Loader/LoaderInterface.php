<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Loader;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface LoaderInterface
{
    /**
     * @throws Throwable If an exception occurs during the loading process, it should be logged and thrown back.
     */
    public function load(): void;
}
