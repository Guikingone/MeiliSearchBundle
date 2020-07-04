<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Synonyms;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResetSynonymsMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $index;

    public function __construct(string $index)
    {
        $this->index = $index;
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
