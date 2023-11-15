<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Search;

use InvalidArgumentException;
use MeiliSearchBundle\Exception\RuntimeException;
use MeiliSearchBundle\Search\FallbackSearchEntrypoint;
use MeiliSearchBundle\Search\SearchEntryPointInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class FallbackSearchEntrypointTest extends TestCase
{
    public function testEntryPointCannotHandleExceptionWithEmptyEntryPoints(): void
    {
        $entryPoint = new FallbackSearchEntrypoint([]);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('No search entrypoint is able to perform the search');
        $entryPoint->search('foo', 'random');
    }

    public function testEntryPointCannotHandleExceptionWithFailedEntryPoints(): void
    {
        $validEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $validEntryPoint->expects(self::once())->method('search')
            ->with(self::equalTo('foo'), self::equalTo('random'), [])
            ->willThrowException(new InvalidArgumentException('Random error'));

        $secondEntrypoint = $this->createMock(SearchEntryPointInterface::class);
        $secondEntrypoint->expects(self::once())->method('search')
            ->with(self::equalTo('foo'), self::equalTo('random'), [])
            ->willThrowException(new InvalidArgumentException('Random error'));

        $entryPoint = new FallbackSearchEntrypoint([
            $validEntryPoint,
            $secondEntrypoint,
        ]);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('No search entrypoint is able to perform the search');
        $entryPoint->search('foo', 'random');
    }

    public function testEntryPointCanHandleException(): void
    {
        $validEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $validEntryPoint->expects(self::once())->method('search')
            ->with(self::equalTo('foo'), self::equalTo('random'), [])
            ->willThrowException(new InvalidArgumentException('Random error'));

        $secondEntrypoint = $this->createMock(SearchEntryPointInterface::class);
        $secondEntrypoint->expects(self::exactly(2))->method('search');

        $entryPoint = new FallbackSearchEntrypoint([
            $validEntryPoint,
            $secondEntrypoint,
        ]);

        $entryPoint->search('foo', 'random');
        $entryPoint->search('foo', 'random');
    }
}
