<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader;

use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader\DocumentReader;
use MeiliSearchBundle\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentReaderTest extends TestCase
{
    public function testClassIsNotDocumentWithInvalidConfiguration(): void
    {
        $reader = new DocumentReader();
        static::assertFalse($reader->isDocument(new stdClass()));
    }

    public function testClassIsNotDocumentWithInvalidConfigurationByMultipleDocumentAttributes(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('Class can not have multiple Document attributes');

        $reader = new DocumentReader();
        $reader->isDocument(new Bar());
    }

    public function testClassIsDocumentWithValidConfiguration(): void
    {
        $reader = new DocumentReader();
        static::assertTrue($reader->isDocument(new Foo()));
    }

    public function testDocumentConfigurationCannotBeAccessedWithoutAttribute(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('Class does not have a Document attribute');
        static::expectExceptionCode(0);

        $reader = new DocumentReader();
        $reader->getConfiguration(new Baz());
    }

    public function testDocumentConfigurationCanBeAccessed(): void
    {
        $reader = new DocumentReader();
        $configuration = $reader->getConfiguration(new Foo());

        static::assertInstanceOf(Document::class, $configuration);
        static::assertSame('foo', $configuration->getIndex());
        static::assertSame('bar', $configuration->getPrimaryKey());
        static::assertTrue($configuration->getModel());
    }
}

#[Document(index: 'foo', primaryKey: 'bar', model: true)]
final class Foo
{
}

#[Document(index: 'foo1', primaryKey: 'bar', model: false)]
/** @phpstan-ignore-next-line */
#[Document(index: 'foo2', primaryKey: 'bar', model: true)]
final class Bar
{
}

final class Baz
{
}
