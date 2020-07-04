<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\AddDocumentMessage;
use MeiliSearchBundle\Messenger\Handler\Document\AddDocumentMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddDocumentMessageHandlerTest extends TestCase
{
    public function testHandlerCanAddADocument(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('addDocument');

        $message = new AddDocumentMessage('foo', [], 'id');

        $handler = new AddDocumentMessageHandler($orchestrator);
        $handler($message);
    }
}
