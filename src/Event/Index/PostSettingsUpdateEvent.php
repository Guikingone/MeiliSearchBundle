<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PostSettingsUpdateEvent extends Event
{
    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var int
     */
    private $update;

    public function __construct(
        Indexes $index,
        int $update
    ) {
        $this->index = $index;
        $this->update = $update;
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    public function getUpdate(): int
    {
        return $this->update;
    }
}
