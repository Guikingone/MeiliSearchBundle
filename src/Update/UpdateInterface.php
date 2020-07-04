<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface UpdateInterface
{
    /**
     * @param string              $status
     * @param int                 $updateId
     * @param array<string,mixed> $type
     * @param float               $duration
     * @param string              $enqueuedAt
     * @param string              $processedAt
     *
     * @return UpdateInterface
     */
    public static function create(
        string $status,
        int $updateId,
        array $type,
        float $duration,
        string $enqueuedAt,
        string $processedAt
    ): UpdateInterface;

    public function getStatus(): string;

    public function getUpdateId(): int;

    /**
     * @return array<string,mixed>
     */
    public function getType(): array;

    public function getDuration(): float;

    public function getEnqueuedAt(): string;

    public function getProcessedAt(): string;
}
