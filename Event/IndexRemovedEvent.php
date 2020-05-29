<?php

namespace MeiliBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRemovedEvent extends Event
{
    private $uid;

    public function __construct(string $uid)
    {
        $this->uid = $uid;
    }

    public function getUid(): string
    {
        return $this->uid;
    }
}
