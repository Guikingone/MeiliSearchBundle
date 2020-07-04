<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Synonyms\UpdateSynonymsMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateSynonymsMessageHandler implements MessageHandlerInterface
{
    /**
     * @var SynonymsOrchestratorInterface
     */
    private $synonymsOrchestrator;

    public function __construct(SynonymsOrchestratorInterface $synonymsOrchestrator)
    {
        $this->synonymsOrchestrator = $synonymsOrchestrator;
    }

    public function __invoke(UpdateSynonymsMessage $message): void
    {
        $this->synonymsOrchestrator->updateSynonyms($message->getIndex(), $message->getSynonyms());
    }
}
