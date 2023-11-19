<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Synonyms\UpdateSynonymsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class UpdateSynonymsMessageHandler
{
    public function __construct(private readonly SynonymsOrchestratorInterface $synonymsOrchestrator)
    {
    }

    public function __invoke(UpdateSynonymsMessage $message): void
    {
        $this->synonymsOrchestrator->updateSynonyms($message->getIndex(), $message->getSynonyms());
    }
}
