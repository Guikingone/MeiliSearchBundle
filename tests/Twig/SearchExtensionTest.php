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
        static::assertNotEmpty($extension->getFunctions());
        static::assertInstanceOf(TwigFunction::class, $extension->getFunctions()[0]);
        static::assertInstanceOf(SearchExtension::class, $extension->getFunctions()[0]->getCallable()[0]);
        static::assertSame('search', $extension->getFunctions()[0]->getCallable()[1]);
    }

    public function testExtensionCanTriggerSearch(): void
    {
        $searchEntryPoint = $this->createMock(SearchEntryPointInterface::class);
        $searchEntryPoint->expects(self::once())->method('search');

        $extension = new SearchExtension($searchEntryPoint);
        $extension->search('foo', 'bar');
    }
}
