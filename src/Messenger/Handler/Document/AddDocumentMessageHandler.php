<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var DocumentEntryPointInterface
     */
    private $documentOrchestrator;

    public function __construct(DocumentEntryPointInterface $documentOrchestrator)
    {
        $this->documentOrchestrator = $documentOrchestrator;
    }

    public function __invoke(AddDocumentMessage $message): void
    {
        $this->documentOrchestrator->addDocument(
            $message->getIndex(),
            $message->getDocument(),
            $message->getPrimaryKey(),
            $message->getModel()
        );
    }
}
