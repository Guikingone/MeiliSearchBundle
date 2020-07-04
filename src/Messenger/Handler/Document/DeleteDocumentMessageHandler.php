<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteDocumentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var DocumentEntryPointInterface
     */
    private $documentOrchestrator;

    public function __construct(DocumentEntryPointInterface $documentOrchestrator)
    {
        $this->documentOrchestrator = $documentOrchestrator;
    }

    public function __invoke(DeleteDocumentMessage $message): void
    {
        $this->documentOrchestrator->removeDocument($message->getIndex(), $message->getDocumentIdentifier());
    }
}
