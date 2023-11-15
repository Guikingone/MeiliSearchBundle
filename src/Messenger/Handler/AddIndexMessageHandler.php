<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\AddIndexMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[AsMessageHandler]
final class AddIndexMessageHandler
{
    public function __construct(private readonly IndexOrchestratorInterface $indexOrchestrator)
    {
    }

    public function __invoke(AddIndexMessage $message): void
    {
        $this->indexOrchestrator->addIndex(
            $message->getUid(),
            $message->getPrimaryKey(),
            $message->getConfiguration()
        );
    }
}
