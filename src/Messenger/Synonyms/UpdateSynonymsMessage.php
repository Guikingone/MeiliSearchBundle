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
     * @var string
     */
    private $index;

    /**
     * @var array<string,array>
     */
    private $synonyms;

    /**
     * @param string              $index
     * @param array<string,array> $synonyms
     */
    public function __construct(string $index, array $synonyms)
    {
        $this->index = $index;
        $this->synonyms = $synonyms;
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
