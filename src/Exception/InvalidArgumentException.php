<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Exception;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
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
