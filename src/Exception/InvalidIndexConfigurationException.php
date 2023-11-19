<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Exception;

use InvalidArgumentException;
use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InvalidIndexConfigurationException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        Throwable $previous = null,
        private readonly ?string $context = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): ?string
    {
        return $this->context;
    }
}
