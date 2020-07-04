<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateDocumentMessageHandler implements MessageHandlerInterface
{
    /**
     * @var DocumentEntryPointInterface
     */
    private $documentOrchestrator;

    public function __construct(DocumentEntryPointInterface $documentOrchestrator)
    {
        $this->documentOrchestrator = $documentOrchestrator;
    }

    public function __invoke(UpdateDocumentMessage $message): void
    {
        $this->documentOrchestrator->updateDocument(
            $message->getIndex(),
            $message->getDocumentUpdate(),
            $message->getPrimaryKey()
        );
    }
}
