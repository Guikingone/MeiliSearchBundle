<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Exception;

use InvalidArgumentException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InvalidSearchConfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    public function getContext(): ?string
    {
        return 'search';
    }
}
