<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Synonyms\ResetSynonymsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class ResetSynonymsMessageHandler
{
    public function __construct(private readonly SynonymsOrchestratorInterface $synonymsOrchestrator)
    {
    }

    public function __invoke(ResetSynonymsMessage $message): void
    {
        $this->synonymsOrchestrator->resetSynonyms($message->getIndex());
    }
}
