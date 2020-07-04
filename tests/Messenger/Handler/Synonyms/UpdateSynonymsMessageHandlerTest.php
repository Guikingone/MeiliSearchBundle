<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Handler\Synonyms\UpdateSynonymsMessageHandler;
use MeiliSearchBundle\Messenger\Synonyms\UpdateSynonymsMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateSynonymsMessageHandlerTest extends TestCase
{
    public function testHandlerCanProcessMessage(): void
    {
        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('updateSynonyms');

        $message = new UpdateSynonymsMessage('foo', []);

        $handler = new UpdateSynonymsMessageHandler($orchestrator);
        $handler($message);
    }
}
