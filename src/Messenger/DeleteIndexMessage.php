<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexMessage
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
