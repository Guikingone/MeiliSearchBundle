<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler;

use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Messenger\AddIndexMessage;
use MeiliSearchBundle\Messenger\Handler\AddIndexMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddIndexMessageHandlerTest extends TestCase
{
    public function testIndexCanBeCreated(): void
    {
        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('addIndex');

        $message = new AddIndexMessage('foo', 'id');

        $handler = new AddIndexMessageHandler($orchestrator);
        $handler($message);
    }
}
