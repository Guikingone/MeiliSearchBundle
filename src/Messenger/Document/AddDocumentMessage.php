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
     * @var string
     */
    private $index;

    /**
     * @var array<string,mixed>
     */
    private $document;

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @var string|null
     */
    private $model;

    /**
     * @param string              $index
     * @param array<string,mixed> $document
     * @param string|null         $primaryKey
     * @param string|null         $model
     */
    public function __construct(
        string $index,
        array $document,
        ?string $primaryKey = null,
        string $model = null
    ) {
        $this->index = $index;
        $this->document = $document;
        $this->primaryKey = $primaryKey;
        $this->model = $model;
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
