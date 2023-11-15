<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Index;

use Exception;
use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Exception\InvalidArgumentException;
use MeiliSearchBundle\Index\IndexSettingsOrchestrator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexSettingsOrchestratorTest extends TestCase
{
    public function testSettingsCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when fetching the settings'),
            [
                'index' => 'foo',
                'error' => 'An error occurred',
            ]
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getSettings')->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client, $logger);

        static::expectException(Exception::class);
        $orchestrator->retrieveSettings('foo');
    }

    public function testSettingsCanBeRetrieved(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getSettings')->willReturn([
            'rankingRules' => [
                'typo',
                'words',
                'proximity',
                'attribute',
                'wordsPosition',
                'exactness',
            ],
            'attributesForFaceting' => [],
            'distinctAttribute' => null,
            'searchableAttributes' => ['title', 'id'],
            'displayedAttributes' => ['title'],
            'stopWords' => null,
            'synonyms' => [],
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client);
        $settings = $orchestrator->retrieveSettings('foo');

        static::assertArrayHasKey('rankingRules', $settings);
        static::assertArrayHasKey('attributesForFaceting', $settings);
        static::assertArrayHasKey('searchableAttributes', $settings);
        static::assertArrayHasKey('attributesForFaceting', $settings);
        static::assertArrayHasKey('displayedAttributes', $settings);
        static::assertArrayHasKey('stopWords', $settings);
        static::assertArrayHasKey('synonyms', $settings);
    }

    public function testSettingsCannotBeUpdatedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when updating the settings'),
            [
                'index' => 'foo',
                'error' => 'An error occurred',
            ]
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateSettings')->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client, $logger);

        static::expectException(Exception::class);
        $orchestrator->updateSettings('foo', [
            'rankingRules' => [
                'words',
                'proximity',
                'attribute',
                'typo',
                'wordsPosition',
                'exactness',
            ],
        ]);
    }

    public function testSettingsCanBeUpdatedWithInvalidKeys(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when updating the settings'),
            [
                'index' => 'foo',
                'error' => 'The following key "test" is not allowed',
            ]
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('getIndex');

        $orchestrator = new IndexSettingsOrchestrator($client, $logger, $eventDispatcher);

        static::expectException(InvalidArgumentException::class);
        $orchestrator->updateSettings('foo', [
            'test' => [
                'words',
                'proximity',
                'attribute',
                'typo',
                'wordsPosition',
                'exactness',
            ],
        ]);
    }

    public function testSettingsCannotBeUpdatedWithEmptyArray(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('updateSettings');

        $client = $this->createMock(Client::class);
        $client->expects(self::never())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client, null, $eventDispatcher);
        $orchestrator->updateSettings('foo', []);
    }

    public function testSettingsCanBeUpdated(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('updateSettings')->willReturn([
            'updateId' => 1,
        ]);

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client, null, $eventDispatcher);
        $orchestrator->updateSettings('foo', [
            'rankingRules' => [
                'words',
                'proximity',
                'attribute',
                'typo',
                'wordsPosition',
                'exactness',
            ],
        ]);
    }

    public function testSettingsCannotBeResetWithInvalidIndex(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when trying to reset the settings'),
            [
                'index' => 'foo',
                'error' => 'An error occurred',
            ]
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::never())->method('resetSettings');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $orchestrator = new IndexSettingsOrchestrator($client, $logger);

        static::expectException(Exception::class);
        $orchestrator->resetSettings('foo');
    }

    public function testSettingsCannotBeResetWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('An error occurred when trying to reset the settings'),
            [
                'index' => 'foo',
                'error' => 'An error occurred',
            ]
        );

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('resetSettings')->willThrowException(new Exception('An error occurred'));

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client, $logger);

        static::expectException(Exception::class);
        $orchestrator->resetSettings('foo');
    }

    public function testSettingsCanBeReset(): void
    {
        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('resetSettings');

        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('getIndex')->willReturn($index);

        $orchestrator = new IndexSettingsOrchestrator($client);
        $orchestrator->resetSettings('foo');
    }
}
