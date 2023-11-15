<?php

declare(strict_types=1);

namespace MeiliSearchBundle\ExpressionLanguage;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

use function array_unshift;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(CacheItemPoolInterface $cache = null, array $providers = [])
    {
        array_unshift($providers, new SearchExpressionLanguageProvider());

        parent::__construct($cache, $providers);
    }
}
