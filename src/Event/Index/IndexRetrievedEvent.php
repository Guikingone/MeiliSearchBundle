<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexRetrievedEvent extends Event implements IndexEventInterface
{
    /**
     * @var Indexes
     */
    private $index;

    public function __construct(Indexes $index)
    {
        $this->index = $index;
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }
}
