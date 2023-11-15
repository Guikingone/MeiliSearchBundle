<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Bridge\Doctrine\Attribute;

use ArgumentCountError;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentTest extends TestCase
{
    public function testDocumentCanBeConfiguredWithInvalidConfigurationAndMissingIndex(): void
    {
        static::expectException(ArgumentCountError::class);
        static::expectExceptionMessage('($index) not passed');
        /* @phpstan-ignore-next-line */
        new Document(
            primaryKey: 'id',
        );
    }

    public function testDocumentCanBeConfiguredWithMissingPrimaryKey(): void
    {
        $document = new Document(
            index: 'foo',
        );

        static::assertNull($document->getPrimaryKey());
    }

    public function testDocumentCanBeConfiguredWithNullPrimaryKey(): void
    {
        $document = new Document(
            index: 'foo',
            primaryKey: null,
        );

        static::assertNull($document->getPrimaryKey());
    }

    public function testDocumentCanBeConfiguredWithInvalidConfigurationOnPrimaryKey(): void
    {
        static::expectExceptionMessage('The primaryKey is not valid');
        new Document(
            index: 'foo',
            primaryKey: '@##',
        );
    }

    public function testDocumentCanBeConfiguredWithInvalidConfigurationOnModel(): void
    {
        static::expectException(TypeError::class);
        static::expectExceptionMessage('($model) must be of type bool, string given');
        new Document(
            index: 'foo',
            primaryKey: 'id',
            model: 'true', /** @phpstan-ignore-line */
        );
    }

    public function testDocumentCanBeConfiguredWithValidConfiguration(): void
    {
        $document = new Document(
            index: 'foo',
            primaryKey: 'id',
            model: true,
        );

        static::assertSame('foo', $document->getIndex());
        static::assertSame('id', $document->getPrimaryKey());
        static::assertTrue($document->getModel());
    }

    public function testDocumentCanBeConfiguredWithValidConfigurationAndWithoutModel(): void
    {
        $document = new Document(
            index: 'foo',
            primaryKey: 'id',
        );

        static::assertSame('foo', $document->getIndex());
        static::assertSame('id', $document->getPrimaryKey());
        static::assertFalse($document->getModel());
    }
}
