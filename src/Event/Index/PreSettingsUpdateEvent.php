<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Event\Index;

use Meilisearch\Endpoints\Indexes;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class PreSettingsUpdateEvent extends Event implements IndexEventInterface
{
    /**
     * @param array<string,array|null> $updatePayload
     */
    public function __construct(private readonly Indexes $index, private readonly array $updatePayload)
    {
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
