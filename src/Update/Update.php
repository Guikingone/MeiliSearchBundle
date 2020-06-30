<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Update
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $updateId;

    /**
     * @var array<string,mixed>
     */
    private $type;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var string
     */
    private $enqueuedAt;

    /**
     * @var string
     */
    private $processedAt;

    public static function create(string $status, int $updateId, array $type, float $duration, string $enqueuedAt, string $processedAt): self
    {
        $self = new self();

        $self->status = $status;
        $self->updateId = $updateId;
        $self->type = $type;
        $self->duration = $duration;
        $self->enqueuedAt = $enqueuedAt;
        $self->processedAt = $processedAt;

        return $self;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUpdateId(): int
    {
        return $this->updateId;
    }

    /**
     * @return array
     */
    public function getType(): array
    {
        return $this->type;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getEnqueuedAt(): string
    {
        return $this->enqueuedAt;
    }

    public function getProcessedAt(): string
    {
        return $this->processedAt;
    }
}
