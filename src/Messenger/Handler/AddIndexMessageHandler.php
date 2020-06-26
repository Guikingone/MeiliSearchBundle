<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\Messenger\Handler;

use MeiliSearchBundle\Client\IndexOrchestratorInterface;
use MeiliSearchBundle\src\Messenger\AddIndexMessage;
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

    public function __invoke(AddIndexMessage $message)
    {
        $this->indexOrchestrator->addIndex($message->getUid(), $message->getPrimaryKey());
    }
}
