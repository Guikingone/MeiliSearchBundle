<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRemovedEvent extends Event implements IndexEventInterface
{
    public function __construct(private readonly string $uid)
    {
    }

    public function getUid(): string
    {
        return $this->uid;
    }
}
