<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use Exception;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Index\IndexOrchestratorInterface;
use MeiliSearchBundle\Result\ResultBuilderInterface;
use MeiliSearchBundle\Search\SearchEntryPoint;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchEntryPointTest extends TestCase
{
    public function testSearchCannotOccurWithExceptionOnIndex(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::equalTo('The search cannot occur as an error occurred when fetching the index'),
            [
                'index' => 'foo',
                'error' => 'An error occurred',
            ]
        );

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->willThrowException(new Exception('An error occurred'));

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $searchEntryPoint = new SearchEntryPoint($orchestrator, $resultBuilder, null, $logger);

        static::expectException(Exception::class);
        static::expectExceptionMessage('An error occurred');
        $searchEntryPoint->search('foo', 'bar');
    }

    public function testSearchCannotOccurWithExceptionOnSearch(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');
        $logger->expects(self::once())->method('error')->with(self::equalTo('The query has failed'));

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('search')->willThrowException(new Exception('An error occurred'));

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $searchEntryPoint = new SearchEntryPoint($orchestrator, $resultBuilder, $eventDispatcher, $logger);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('An error occurred');
        $searchEntryPoint->search('foo', 'bar');
    }

    public function testSearchCanOccurWithoutModels(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('search')->willReturn([
            'hits' => [
                [
                    'id' => 1,
                    'title' => 'bar',
                ],
                [
                    'id' => 2,
                    'title' => 'foo',
                ],
            ],
            "offset" => 0,
            "limit" => 20,
            "nbHits" => 2,
            "exhaustiveNbHits" => false,
            "processingTimeMs" => 35,
            "query" => 'bar'
        ]);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);

        $searchEntryPoint = new SearchEntryPoint($orchestrator, $resultBuilder, $eventDispatcher, $logger);
        $result = $searchEntryPoint->search('foo', 'bar');

        static::assertNotEmpty($result->getHits());
        static::assertSame(2, $result->count());
        static::assertSame(20, $result->getLimit());
        static::assertSame(0, $result->getOffset());
        static::assertSame('bar', $result->getQuery());
    }

    public function testSearchCanOccurWithModels(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::exactly(2))->method('dispatch');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('info');

        $index = $this->createMock(Indexes::class);
        $index->expects(self::once())->method('search')->willReturn([
            'hits' => [
                [
                    'id' => 1,
                    'title' => 'bar',
                    'model' => FooModel::class,
                ],
                [
                    'id' => 2,
                    'title' => 'foo',
                    'model' => FooModel::class,
                ],
            ],
            "offset" => 0,
            "limit" => 20,
            "nbHits" => 2,
            "exhaustiveNbHits" => false,
            "processingTimeMs" => 35,
            "query" => 'bar'
        ]);

        $orchestrator = $this->createMock(IndexOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('getIndex')->willReturn($index);

        $resultBuilder = $this->createMock(ResultBuilderInterface::class);
        $resultBuilder->expects(self::exactly(2))->method('support')->willReturn(true);
        $resultBuilder->expects(self::exactly(2))->method('build')->willReturnOnConsecutiveCalls(
            FooModel::create(1, 'foo'),
            FooModel::create(2, 'bar')
        );

        $searchEntryPoint = new SearchEntryPoint($orchestrator, $resultBuilder, $eventDispatcher, $logger);
        $result = $searchEntryPoint->search('foo', 'bar');

        static::assertNotEmpty($result->getHits());
        static::assertSame(2, $result->count());
        static::assertSame(20, $result->getLimit());
        static::assertSame(0, $result->getOffset());
        static::assertSame('bar', $result->getQuery());
    }
}

final class FooModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    public static function create(int $id, string $title): FooModel
    {
        $self = new self();

        $self->id = $id;
        $self->title = $title;

        return $self;
    }
}
