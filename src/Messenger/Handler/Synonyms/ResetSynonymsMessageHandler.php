<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Synonyms\ResetSynonymsMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResetSynonymsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SynonymsOrchestratorInterface
     */
    private $synonymsOrchestrator;

    public function __construct(SynonymsOrchestratorInterface $synonymsOrchestrator)
    {
        $this->synonymsOrchestrator = $synonymsOrchestrator;
    }

    public function __invoke(ResetSynonymsMessage $message): void
    {
        $this->synonymsOrchestrator->resetSynonyms($message->getIndex());
    }
}
