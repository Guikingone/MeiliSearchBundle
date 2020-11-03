<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use MeiliSearchBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use ReflectionClass;
use stdClass;
use function get_class;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentReaderTest extends TestCase
{
    public function testClassIsNotDocumentWithInvalidConfiguration(): void
    {
        $annotationReader = new AnnotationReader();

        $reader = new DocumentReader($annotationReader);
        static::assertFalse($reader->isDocument(new stdClass()));
    }

    public function testClassIsDocumentWithValidConfiguration(): void
    {
        $annotationReader = new AnnotationReader();

        $reader = new DocumentReader($annotationReader);
        static::assertTrue($reader->isDocument(new Foo()));
    }

    public function testDocumentConfigurationCannotBeAccessedWithInvalidAnnotation(): void
    {
        $annotationReader = $this->createMock(AnnotationReader::class);
        $annotationReader->expects(self::once())->method('getClassAnnotation')
            ->with(new ReflectionClass(get_class(new Foo())), Document::class)
            ->willReturn(stdClass::class)
        ;

        $reader = new DocumentReader($annotationReader);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('The current object is not a document');
        static::expectExceptionCode(0);
        $reader->getConfiguration(new Foo());
    }

    public function testDocumentConfigurationCanBeAccessed(): void
    {
        $annotationReader = new AnnotationReader();

        $reader = new DocumentReader($annotationReader);
        $configuration = $reader->getConfiguration(new Foo());

        static::assertInstanceOf(Document::class, $configuration);
        static::assertSame('foo', $configuration->getIndex());
        static::assertSame('bar', $configuration->getPrimaryKey());
        static::assertTrue($configuration->getModel());
    }
}

/**
 * @Document(index="foo", primaryKey="bar", model=true)
 */
final class Foo
{
}
