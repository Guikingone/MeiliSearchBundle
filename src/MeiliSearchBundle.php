<?php

declare(strict_types=1);

namespace MeiliSearchBundle;

use MeiliSearchBundle\DependencyInjection\MeiliSearchBundlePass;
use MeiliSearchBundle\DependencyInjection\MeiliSearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MeiliSearchExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MeiliSearchBundlePass());
    }
}
