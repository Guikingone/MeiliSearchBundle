<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Dump;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpCreatedEvent extends Event
{
    public function __construct(private readonly string $uid)
    {
    }

    public function getUid(): string
    {
        return $this->uid;
    }
}
