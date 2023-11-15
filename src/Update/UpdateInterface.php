<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface UpdateInterface
{
    /**
     * @param int|null $canceledBy
     * @param array<string,mixed> $details
     * @param array<string,string>|null $error
     */
    public static function create(
        int $uid,
        string $indexUid,
        string $status,
        string $type,
        int|null $canceledBy,
        array $details,
        array|null $error,
        string $duration,
        string $enqueuedAt,
        string $startedAt,
        string $finishedAt,
    ): UpdateInterface;

    public function getUid(): int;

    public function getIndexUid(): string;

    public function getStatus(): string;

    public function getType(): string;

    public function getCanceledBy(): ?int;

    /**
     * @return array<string,mixed>
     */
    public function getDetails(): array;

    /**
     * @return array<string,string>|null
     */
    public function getError(): ?array;

    public function getDuration(): string;

    public function getEnqueuedAt(): string;

    public function getStartedAt(): string;

    public function getFinishedAt(): string;
}
