<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Client;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface ClientInterface
{
    public function createIndex(string $uid, string $primaryKey = null): void;

    public function deleteIndex(string $uid): void;

    /**
     * @return array<int, string>
     */
    public function getIndexes(): array;

    public function deleteIndexes(): void;

    /**
     * @param string                    $index
     * @param string                    $query
     * @param array<string, mixed>|null $options
     *
     * @return array<string, mixed>
     */
    public function search(string $index, string $query, array $options = null): array;

    /**
     * @return array<string, mixed>
     */
    public function getSystemInformations(): array;
}
