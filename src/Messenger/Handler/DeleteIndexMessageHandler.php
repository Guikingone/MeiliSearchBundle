<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexMessageHandler implements MessageHandlerInterface
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    public function __construct(IndexOrchestratorInterface $indexOrchestrator)
    {
        $this->indexOrchestrator = $indexOrchestrator;
    }

    public function __invoke(DeleteIndexMessage $message): void
    {
        $this->indexOrchestrator->removeIndex($message->getIndex());
    }
}
