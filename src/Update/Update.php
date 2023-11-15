<?php

declare(strict_types=1);

namespace MeiliSearchBundle\Update;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class Update implements UpdateInterface
{
    private int $uid;

    private string $indexUid;

    private string $status;

    private string $type;

    private int|null $canceledBy = null;

    /**
     * @var array<string,mixed>
     */
    private array $details = [];

    /**
     * @var array<string,string>|null
     */
    private array|null $error = null;

    private string $duration;

    private string $enqueuedAt;

    private string $startedAt;

    private string $finishedAt;

    /**
     * {@inheritdoc}
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
    ): UpdateInterface {
        $self = new self();

        $self->uid = $uid;
        $self->indexUid = $indexUid;
        $self->status = $status;
        $self->type = $type;
        $self->canceledBy = $canceledBy;
        $self->details = $details;
        $self->error = $error;
        $self->duration = $duration;
        $self->enqueuedAt = $enqueuedAt;
        $self->startedAt = $startedAt;
        $self->finishedAt = $finishedAt;

        return $self;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getIndexUid(): string
    {
        return $this->indexUid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCanceledBy(): ?int
    {
        return $this->canceledBy;
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): ?array
    {
        return $this->error;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function getEnqueuedAt(): string
    {
        return $this->enqueuedAt;
    }

    public function getStartedAt(): string
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): string
    {
        return $this->finishedAt;
    }
}
