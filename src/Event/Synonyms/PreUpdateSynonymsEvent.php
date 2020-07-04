<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Synonyms;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreUpdateSynonymsEvent extends Event
{
    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var array<string,array>
     */
    private $synonyms;

    /**
     * @param Indexes             $index
     * @param array<string,array> $synonyms
     */
    public function __construct(
        Indexes $index,
        array $synonyms
    ) {
        $this->index = $index;
        $this->synonyms = $synonyms;
    }

    public function getIndex(): Indexes
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
