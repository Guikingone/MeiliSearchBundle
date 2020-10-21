<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Annotation\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\ConfigurationAnnotationInterface;
use MeiliSearchBundle\Bridge\Doctrine\Annotation\Document;
use MeiliSearchBundle\Exception\RuntimeException;
use ReflectionClass;
use ReflectionException;
use function get_class;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class DocumentReader implements DocumentReaderInterface
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function isDocument(object $object): bool
    {
        $class = new ReflectionClass(get_class($object));

        return $this->reader->getClassAnnotation($class, Document::class) instanceof Document;
    }

    /**
     * @param object $object
     *
     * @return ConfigurationAnnotationInterface|Document
     *
     * @throws ReflectionException
     */
    public function getConfiguration(object $object): ConfigurationAnnotationInterface
    {
        $reflectionClass = new ReflectionClass(get_class($object));

        $annotation = $this->reader->getClassAnnotation($reflectionClass, Document::class);
        if (!$annotation instanceof ConfigurationAnnotationInterface) {
            throw new RuntimeException('The current object is not a document');
        }

        return $annotation;
    }
}
