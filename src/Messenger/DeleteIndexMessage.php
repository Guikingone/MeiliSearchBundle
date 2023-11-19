<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexMessage implements MessageInterface
{
    public function __construct(private readonly string $index)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
