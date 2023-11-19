<?php

declare(strict_types=1);

namespace MeiliSearchBundle\EventSubscriber;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
interface MeiliSearchEventSubscriberInterface
{
    public const LOG_MASK = '[MeiliSearch] %s';

    public const INDEX_LOG_KEY = 'index';

    public const UPDATE_LOG_KEY = 'update';
}
