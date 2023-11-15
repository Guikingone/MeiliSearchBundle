<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle\Twig;

use MeiliSearchBundle\Search\SearchEntryPointInterface;
use MeiliSearchBundle\Twig\SearchExtension;
use PHPUnit\Framework\TestCase;
use Twig\Extension\RuntimeExtensionInterface;
use Twig\TwigFunction;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class SearchExtensionTest extends TestCase
{
    public function testExtensionRegisterFunction(): void
    {
        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);

        $extension = new SearchExtension($searchEntryPoint);

        static::assertInstanceOf(RuntimeExtensionInterface::class, $extension);
        $functions = $extension->getFunctions();
        static::assertNotEmpty($functions);
        $function = $functions[0];
        static::assertInstanceOf(TwigFunction::class, $function);
        /** @var array{class-string, string} $callable */
        $callable = $function->getCallable();
        static::assertInstanceOf(SearchExtension::class, $callable[0]);
        static::assertSame('search', $function->getName());
    }

    public function testExtensionCanTriggerSearch(): void
    {
        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $searchEntryPoint->expects(self::once())->method('search');

        $extension = new SearchExtension($searchEntryPoint);
        $extension->search('foo', 'bar');
    }
}
