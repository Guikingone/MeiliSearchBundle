<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Settings;

use Meilisearch\Endpoints\Indexes;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Settings\SettingsEntryPoint;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SettingsEntryPointTest extends TestCase
{
    public function testSettingsCannotBeRetrievedWithException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(self::equalTo('The settings cannot be retrieved'), [
            'error' => 'An error occurred',
        ]);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->willThrowException(
            new RuntimeException('An error occurred')
        );

        $entryPoint = new SettingsEntryPoint($orchestrator, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        static::expectExceptionCode(0);
        $entryPoint->getSettings('foo');
    }

    public function testSettingsCanBeRetrieved(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('getSettings')->willReturn([
            'rankingRules' => [
                'typo',
                'words',
                'proximity',
                'attribute',
                'wordsPosition',
                'exactness',
                'desc(release_date)',
            ],
            'attributesForFaceting' => ['genre'],
            'distinctAttribute' => null,
            'searchableAttributes' => ['title', 'description', 'uid'],
            'displayedAttributes' => [
                'title',
                'description',
                'release_date',
                'rank',
                'poster',
            ],
            'stopWords' => null,
            'synonyms' => [
                'wolverine' => ['xmen', 'logan'],
                'logan' => ['wolverine', 'xmen'],
            ],
        ]);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->with(self::equalTo('foo'))->willReturn($index);

        $entryPoint = new SettingsEntryPoint($orchestrator, $logger);

        $settings = $entryPoint->getSettings('foo');

        static::assertEquals([
            'typo',
            'words',
            'proximity',
            'attribute',
            'wordsPosition',
            'exactness',
            'desc(release_date)',
        ], $settings->getRankingRules());
        static::assertContains('genre', $settings->getAttributesForFaceting());
        static::assertNull($settings->getDistinctAttribute());
        static::assertEquals(['title', 'description', 'uid'], $settings->getSearchableAttributes());
        static::assertEquals([
            'title',
            'description',
            'release_date',
            'rank',
            'poster',
        ], $settings->getDisplayedAttributes());
        static::assertNull($settings->getStopWords());
        /** @var array<string, array<int, string>> $synonyms */
        $synonyms = $settings->getSynonyms();
        static::assertArrayHasKey('wolverine', $synonyms);
        static::assertArrayHasKey('logan', $synonyms);
    }
}
