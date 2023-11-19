<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class AddDocumentMessageHandler
{
    public function __construct(private readonly DocumentEntryPointInterface $documentOrchestrator)
    {
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
