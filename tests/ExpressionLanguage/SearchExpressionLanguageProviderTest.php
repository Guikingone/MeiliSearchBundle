<?php

declare(strict_types=1);

namespace ExpressionLanguage;

use MeiliSearchBundle\ExpressionLanguage\SearchExpressionLanguageProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExpressionLanguageProviderTest extends TestCase
{
    public function testProviderIsRegistered(): void
    {
        $provider = new SearchExpressionLanguageProvider();

        static::assertNotEmpty($provider->getFunctions());
        static::assertInstanceOf(ExpressionFunction::class, $provider->getFunctions()[0]);
        static::assertSame('search', $provider->getFunctions()[0]->getName());
    }
}
