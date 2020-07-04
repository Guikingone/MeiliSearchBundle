<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\DeleteIndexMessage;
use MeiliSearchBundle\Messenger\Handler\DeleteIndexMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteIndexMessageHandlerTest extends TestCase
{
    public function testHandlerCanDeleteIndex(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('removeIndex');

        $message = new DeleteIndexMessage('foo');

        $handler = new DeleteIndexMessageHandler($orchestrator);
        $handler($message);
    }
}
