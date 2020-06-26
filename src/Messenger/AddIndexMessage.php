<?php

declare(strict_types=1);

namespace MeiliSearchBundle\src\Messenger;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class AddIndexMessage
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string|null
     */
    private $primaryKey;

    public function __construct(string $uid, ?string $primaryKey = null)
    {
        $this->uid = $uid;
        $this->primaryKey = $primaryKey;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }
}
