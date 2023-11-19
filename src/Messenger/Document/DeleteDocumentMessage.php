<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Document;

use MeiliSearchBundle\Messenger\MessageInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteDocumentMessage implements MessageInterface
{
    /**
     * @param string|int $documentIdentifier
     */
    public function __construct(private readonly string $index, private $documentIdentifier)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return string|int
     */
    public function getDocumentIdentifier()
    {
        return $this->documentIdentifier;
    }
}
