<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\DeleteDocumentMessage;
use MeiliSearchBundle\Messenger\Handler\Document\DeleteDocumentMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DeleteDocumentMessageHandlerTest extends TestCase
{
    public function testHandlerCanDeleteADocument(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('removeDocument');

        $message = new DeleteDocumentMessage('foo', 'random');

        $handler = new DeleteDocumentMessageHandler($orchestrator);
        $handler($message);
    }
}
