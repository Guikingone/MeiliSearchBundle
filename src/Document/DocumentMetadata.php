<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Document;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentMetadata
{
    private const DEFAULT_TYPE = 'array';

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $index, ?string $type = self::DEFAULT_TYPE)
    {
        $this->index = $index;
        $this->type = $type;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
