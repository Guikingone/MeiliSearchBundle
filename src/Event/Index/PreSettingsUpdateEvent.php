<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use MeiliSearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSettingsUpdateEvent extends Event implements IndexEventInterface
{
    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var array<string,array|null>
     */
    private $updatePayload;

    /**
     * @param Indexes                  $index
     * @param array<string,array|null> $updatePayload
     */
    public function __construct(
        Indexes $index,
        array $updatePayload
    ) {
        $this->index = $index;
        $this->updatePayload = $updatePayload;
    }

    public function getIndex(): Indexes
    {
        return $this->index;
    }

    /**
     * @return array<string,array|null>
     */
    public function getUpdatePayload(): array
    {
        return $this->updatePayload;
    }
}
