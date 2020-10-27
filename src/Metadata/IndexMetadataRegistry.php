<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

use MeiliSearchBundle\Exception\InvalidArgumentException;
use function array_key_exists;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class IndexMetadataRegistry
{
    /**
     * @var array<string, IndexMetadata>
     */
    private $indexes = [];

    public function add(string $index, IndexMetadata $configuration): void
    {
        if ($this->has($index)) {
            $this->override($index, $configuration);

            return;
        }

        $this->indexes[$index] = $configuration;
    }

    public function override(string $index, IndexMetadata $newConfiguration): void
    {
        if (!$this->has($index)) {
            $this->add($index, $newConfiguration);

            return;
        }

        $this->indexes[$index] = $newConfiguration;
    }

    public function get(string $index): IndexMetadata
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('The desired index does not exist');
        }

        return $this->indexes[$index];
    }

    public function remove(string $index): void
    {
        if (!$this->has($index)) {
            throw new InvalidArgumentException('The desired index does not exist');
        }

        unset($this->indexes[$index]);
    }

    public function clear(): void
    {
        if (empty($this->indexes)) {
            return;
        }

        $this->indexes = [];
    }

    /**
     * @return array<string, IndexMetadata>
     */
    public function toArray(): array
    {
        return $this->indexes;
    }

    private function has(string $index): bool
    {
        return array_key_exists($index, $this->indexes);
    }
}
