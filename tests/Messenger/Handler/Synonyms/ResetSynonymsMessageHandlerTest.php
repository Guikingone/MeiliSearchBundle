<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler\Synonyms;

use MeiliSearchBundle\Index\SynonymsOrchestratorInterface;
use MeiliSearchBundle\Messenger\Handler\Synonyms\ResetSynonymsMessageHandler;
use MeiliSearchBundle\Messenger\Synonyms\ResetSynonymsMessage;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResetSynonymsMessageHandlerTest extends TestCase
{
    public function testHandlerCanProcessMessage(): void
    {
        $orchestrator = $this->createMock(SynonymsOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('resetSynonyms')->with(self::equalTo('foo'));

        $message = new ResetSynonymsMessage('foo');

        $handler = new ResetSynonymsMessageHandler($orchestrator);
        $handler($message);
    }
}
