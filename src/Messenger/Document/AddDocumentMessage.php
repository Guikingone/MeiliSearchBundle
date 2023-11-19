<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessage implements MessageInterface
{
    /**
     * @param array<string,mixed> $document
     * @param string|null $primaryKey
     * @param string|null $model
     */
    public function __construct(
        private readonly string $index,
        private readonly array $document,
        private readonly ?string $primaryKey = null,
        private readonly ?string $model = null
    ) {
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDocument(): array
    {
        return $this->document;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }
}
