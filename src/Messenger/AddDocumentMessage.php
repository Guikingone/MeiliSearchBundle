<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\Messenger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessage
{
    /**
     * @var string
     */
    private $index;

    /**
     * @var array<mixed,mixed>
     */
    private $document;

    /**
     * @var string|null
     */
    private $primaryKey;

    public function __construct(string $index, array $document, ?string $primaryKey = null)
    {
        $this->index = $index;
        $this->document = $document;
        $this->primaryKey = $primaryKey;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array
     */
    public function getDocument(): array
    {
        return $this->document;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }
}
