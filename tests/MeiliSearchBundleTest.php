<?php

declare(strict_types=1);

namespace Tests\MeiliSearchBundle;

use MeiliSearchBundle\DependencyInjection\MeiliSearchExtension;
use MeiliSearchBundle\MeiliSearchBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundleTest extends TestCase
{
    public function testExtensionIsDefined(): void
    {
        $bundle = new MeiliSearchBundle();

        static::assertInstanceOf(MeiliSearchExtension::class, $bundle->getContainerExtension());
    }

    public function testCompilerPassIsSet(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(self::once())->method('addCompilerPass');

        $bundle = new MeiliSearchBundle();
        $bundle->build($container);
    }
}
