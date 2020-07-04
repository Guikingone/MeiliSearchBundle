<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Annotation;

use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentTest extends TestCase
{
    public function testDocumentCanBeConfiguredWithInvalidConfigurationAndMissingIndex(): void
    {
        static::expectException(InvalidDocumentConfigurationException::class);
        static::expectExceptionMessage('The index must be defined');
        new Document([
            'primaryKey' => 'id',
        ]);
    }

    public function testDocumentCanBeConfiguredWithMissingPrimaryKey(): void
    {
        $document = new Document([
            'index' => 'foo',
        ]);

        static::assertNull($document->getPrimaryKey());
    }

    public function testDocumentCanBeConfiguredWithNullPrimaryKey(): void
    {
        $document = new Document([
            'index' => 'foo',
            'primaryKey' => null,
        ]);

        static::assertNull($document->getPrimaryKey());
    }

    public function testDocumentCanBeConfiguredWithInvalidConfigurationOnPrimaryKey(): void
    {
        static::expectException(InvalidDocumentConfigurationException::class);
        static::expectExceptionMessage('The primaryKey is not valid');
        new Document([
            'index' => 'foo',
            'primaryKey' => '@##',
        ]);
    }

    public function testDocumentCanBeConfiguredWithInvalidConfigurationOnModel(): void
    {
        static::expectException(InvalidDocumentConfigurationException::class);
        new Document([
            'index' => 'foo',
            'primaryKey' => 'id',
            'model' => 'true',
        ]);
    }

    public function testDocumentCanBeConfiguredWithValidConfiguration(): void
    {
        $document = new Document([
            'index' => 'foo',
            'primaryKey' => 'id',
            'model' => true,
        ]);

        static::assertSame('foo', $document->getIndex());
        static::assertSame('id', $document->getPrimaryKey());
        static::assertTrue($document->getModel());
    }

    public function testDocumentCanBeConfiguredWithValidConfigurationAndWithoutModel(): void
    {
        $document = new Document([
            'index' => 'foo',
            'primaryKey' => 'id',
        ]);

        static::assertSame('foo', $document->getIndex());
        static::assertSame('id', $document->getPrimaryKey());
        static::assertFalse($document->getModel());
    }
}
