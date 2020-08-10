<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;
use function array_key_exists;
use function gettype;
use function is_bool;
use function preg_match;
use function sprintf;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class Document implements ConfigurationAnnotationInterface
{
    private const REGEX_PATTERN = '#[A-Za-z0-9-_]+#';
    private const PRIMARY_KEY = 'primaryKey';
    private const MODEL = 'model';

    /**
     * @var string
     */
    private $index;

    /**
     * @var string|null
     *
     * @see https://docs.meilisearch.com/guides/main_concepts/documents.html#primary-key
     */
    private $primaryKey;

    /**
     * @var bool
     */
    private $model = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration = [])
    {
        if (!array_key_exists('index', $configuration)) {
            throw new InvalidDocumentConfigurationException('The index must be defined');
        }

        $this->index = $configuration['index'];

        if ((array_key_exists(self::PRIMARY_KEY, $configuration) && null !== $configuration[self::PRIMARY_KEY]) && !$this->primaryKeyIsValid($configuration[self::PRIMARY_KEY])) {
            throw new InvalidDocumentConfigurationException('The primaryKey is not valid');
        }

        $this->primaryKey = array_key_exists(self::PRIMARY_KEY, $configuration) ? $configuration[self::PRIMARY_KEY] : null;

        if (array_key_exists(self::MODEL, $configuration) && !is_bool($configuration[self::MODEL])) {
            throw new InvalidDocumentConfigurationException(sprintf(
                'The model key must be a bool, given "%s"',
                gettype($configuration[self::MODEL])
            ));
        }

        $this->model = $configuration[self::MODEL] ?? false;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function getModel(): bool
    {
        return $this->model;
    }

    private function primaryKeyIsValid(string $primaryKey): bool
    {
        return (bool) preg_match(self::REGEX_PATTERN, $primaryKey);
    }
}
