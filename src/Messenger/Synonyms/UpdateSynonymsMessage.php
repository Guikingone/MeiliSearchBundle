<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Synonyms;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateSynonymsMessage implements MessageInterface
{
    /**
     * @param array<string,array> $synonyms
     */
    public function __construct(private readonly string $index, private readonly array $synonyms)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<string,array>
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
    }
}
