<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\Messenger\Handler;

use MeiliSearchBundle\Client\DocumentOrchestratorInterface;
use MeiliSearchBundle\Messenger\AddDocumentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var DocumentOrchestratorInterface
     */
    private $documentOrchestrator;

    public function __construct(DocumentOrchestratorInterface $documentOrchestrator)
    {
        $this->documentOrchestrator = $documentOrchestrator;
    }

    public function __invoke(AddDocumentMessage $message): void
    {
        $this->documentOrchestrator->addDocument($message->getIndex(), $message->getDocument(), $message->getPrimaryKey());
    }
}
