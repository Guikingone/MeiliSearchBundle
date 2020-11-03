<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Metadata;

use Countable;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface IndexMetadataRegistryInterface extends Countable
{
    public function add(string $index, IndexMetadataInterface $metadata): void;

    public function override(string $index, IndexMetadataInterface $newConfiguration): void;

    public function get(string $index): IndexMetadataInterface;

    public function remove(string $index): void;

    public function has(string $index): bool;

    public function clear(): void;

    /**
     * @return array<string, IndexMetadataInterface>
     */
    public function toArray(): array;
}
