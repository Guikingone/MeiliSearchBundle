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
     * @var string
     */
    private $index;

    /**
     * @var array<string,mixed>
     */
    private $documentUpdate;

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @param string              $index
     * @param array<string,mixed> $documentUpdate
     * @param string|null         $primaryKey
     */
    public function __construct(
        string $index,
        array $documentUpdate,
        ?string $primaryKey = null
    ) {
        $this->index = $index;
        $this->documentUpdate = $documentUpdate;
        $this->primaryKey = $primaryKey;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<string,mixed>
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
