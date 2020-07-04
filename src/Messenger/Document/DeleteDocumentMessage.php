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
     * @var string
     */
    private $index;

    /**
     * @var string|int
     */
    private $documentIdentifier;

    /**
     * @param string     $index
     * @param string|int $documentIdentifier
     */
    public function __construct(string $index, $documentIdentifier)
    {
        $this->index = $index;
        $this->documentIdentifier = $documentIdentifier;
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
