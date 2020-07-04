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
     * @var string|null
     */
    private $context;

    /**
     * {@inheritdoc}
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, string $context = null)
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    public function getContext(): ?string
    {
        return $this->context;
    }
}
