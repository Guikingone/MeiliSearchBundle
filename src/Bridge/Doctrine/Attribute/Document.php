<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Bridge\Doctrine\Attribute;

use Attribute;
use MeiliSearchBundle\Exception\InvalidDocumentConfigurationException;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Document implements ConfigurationAttributeInterface
{
    private const REGEX_PATTERN = '#[A-Za-z0-9-_]+#';

    public function __construct(
        private readonly string $index,
        private readonly string|null $primaryKey = null,
        private readonly bool $model = false
    ) {
        if (null === $this->primaryKey) {
            return;
        }
        if ($this->primaryKeyIsValid($this->primaryKey)) {
            return;
        }
        throw new InvalidDocumentConfigurationException('The primaryKey is not valid');
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
        return (bool)preg_match(self::REGEX_PATTERN, $primaryKey);
    }
}
