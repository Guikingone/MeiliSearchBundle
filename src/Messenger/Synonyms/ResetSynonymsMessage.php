<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Synonyms;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResetSynonymsMessage implements MessageInterface
{
    public function __construct(private readonly string $index)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
