<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Dump;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DumpCreatedEvent extends Event
{
    /**
     * @var string
     */
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
