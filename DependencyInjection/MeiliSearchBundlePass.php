<?php

namespace MeiliBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundlePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
    }
}
