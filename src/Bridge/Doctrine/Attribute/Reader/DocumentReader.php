<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Attribute\Reader;

use MeiliSearchBundle\Bridge\Doctrine\Attribute\ConfigurationAttributeInterface;
use MeiliSearchBundle\Bridge\Doctrine\Attribute\Document;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;
use MeiliSearchBundle\Exception\RuntimeException;
use ReflectionAttribute;
use ReflectionClass;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentReader implements DocumentReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function isDocument(object $object): bool
    {
        $reflectionClass = new ReflectionClass($object::class);
        $attributes = $reflectionClass->getAttributes(Document::class, ReflectionAttribute::IS_INSTANCEOF);

        $attributeCount = count($attributes);
        if ($attributeCount > 1) {
            throw new RuntimeException('Class can not have multiple Document attributes');
        }

        if ($attributeCount === 0) {
            return false;
        }

        return $attributes[0]->newInstance() instanceof Document;
    }

    /**
     * @return ConfigurationAttributeInterface|Document
     */
    public function getConfiguration(object $object): ConfigurationAttributeInterface|Document
    {
        $reflectionClass = new ReflectionClass($object::class);
        $attributes = $reflectionClass->getAttributes(Document::class, ReflectionAttribute::IS_INSTANCEOF);

        $attributeCount = count($attributes);
        if (count($attributes) > 1) {
            throw new RuntimeException('Class can not have multiple Document attributes');
        }

        if ($attributeCount === 0) {
            throw new RuntimeException('Class does not have a Document attribute');
        }

        $instance = $attributes[0]->newInstance();
        if (!$instance instanceof ConfigurationAttributeInterface) {
            throw new InvalidDocumentConfigurationException('The current object is not a document');
        }

        return $instance;
    }
}
