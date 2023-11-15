<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateDocumentMessage implements MessageInterface
{
    /**
     * @param array<string, mixed> $documentUpdate
     * @param string|null $primaryKey
     */
    public function __construct(
        private readonly string $index,
        private readonly array $documentUpdate,
        private readonly ?string $primaryKey = null
    ) {
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDocumentUpdate(): array
    {
        return $this->documentUpdate;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }
}
