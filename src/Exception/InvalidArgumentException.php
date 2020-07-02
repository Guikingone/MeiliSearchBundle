<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Exception;

use Throwable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @var string
     */
    private $context;

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
