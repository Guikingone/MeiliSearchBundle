<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Result;

use Exception;
use MeiliSearchBundle\Result\ResultBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class ResultBuilderTest extends TestCase
{
    public function testBuilderSupport(): void
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::never())->method('denormalize');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $builder = new ResultBuilder($serializer, $logger);

        static::assertFalse($builder->support(['test' => 'foo']));
        static::assertTrue($builder->support(['model' => stdClass::class]));
    }

    public function testDataCannotBeBuiltWithExceptionAndLogger(): void
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('denormalize')->willThrowException(
            new Exception('An error occurred')
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $builder = new ResultBuilder($serializer, $logger);

        static::expectException(Exception::class);
        $builder->build([
            'id' => 1,
            'key' => 'bar',
            'model' => Foo::class,
        ]);
    }

    public function testDataCannotBeBuiltWithExceptionAndWithoutLogger(): void
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('denormalize')->willThrowException(
            new Exception('An error occurred')
        );

        $builder = new ResultBuilder($serializer);

        static::expectException(Exception::class);
        $builder->build([
            'id' => 1,
            'key' => 'bar',
            'model' => Foo::class,
        ]);
    }

    public function testDataCanBeBuilt(): void
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        $builder = new ResultBuilder($serializer);
        $data = $builder->build([
            'id' => 1,
            'key' => 'bar',
            'model' => Foo::class,
        ]);

        static::assertInstanceOf(Foo::class, $data);
        static::assertSame(1, $data->id);
        static::assertSame('bar', $data->key);
    }
}

final class Foo
{
    public int $id;

    public string $key;
}
