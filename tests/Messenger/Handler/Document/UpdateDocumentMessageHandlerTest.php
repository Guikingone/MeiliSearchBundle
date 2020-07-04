<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Messenger\Handler\Document;

use MeiliSearchBundle\Document\DocumentEntryPointInterface;
use MeiliSearchBundle\Messenger\Document\UpdateDocumentMessage;
use MeiliSearchBundle\Messenger\Handler\Document\UpdateDocumentMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class UpdateDocumentMessageHandlerTest extends TestCase
{
    public function testDocumentCanBeUpdated(): void
    {
        $orchestrator = $this->createMock(DocumentEntryPointInterface::class);
        $orchestrator->expects(self::once())->method('updateDocument');

        $message = new UpdateDocumentMessage('foo', [
            'id' => 1,
            'title' => 'bar',
        ]);

        $handler = new UpdateDocumentMessageHandler($orchestrator);
        $handler($message);
    }
}
