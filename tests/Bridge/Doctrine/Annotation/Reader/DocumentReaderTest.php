<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader\DocumentReader;
use PHPUnit\Framework\TestCase;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use stdClass;

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
