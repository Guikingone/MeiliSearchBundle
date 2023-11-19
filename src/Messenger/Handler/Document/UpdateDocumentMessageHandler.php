<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class UpdateDocumentMessageHandler
{
    public function __construct(private readonly DocumentEntryPointInterface $documentOrchestrator)
    {
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
