<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class DeleteDocumentMessageHandler
{
    public function __construct(private readonly DocumentEntryPointInterface $documentOrchestrator)
    {
    }

    public function __invoke(DeleteDocumentMessage $message): void
    {
        $this->documentOrchestrator->removeDocument($message->getIndex(), $message->getDocumentIdentifier());
    }
}
