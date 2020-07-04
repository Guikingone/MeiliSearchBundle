<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\AddIndexMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddIndexMessageHandler implements MessageHandlerInterface
{
    /**
     * @var IndexOrchestratorInterface
     */
    private $indexOrchestrator;

    public function __construct(IndexOrchestratorInterface $indexOrchestrator)
    {
        $this->indexOrchestrator = $indexOrchestrator;
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
