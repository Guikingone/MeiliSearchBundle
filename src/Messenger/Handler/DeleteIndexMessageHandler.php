<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class DeleteIndexMessageHandler
{
    public function __construct(private readonly IndexOrchestratorInterface $indexOrchestrator)
    {
    }

    public function __invoke(DeleteIndexMessage $message): void
    {
        $this->indexOrchestrator->removeIndex($message->getIndex());
    }
}
