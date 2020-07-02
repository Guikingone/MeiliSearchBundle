<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Exception;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ExceptionInterface
{
    public function getContext(): ?string;
}
