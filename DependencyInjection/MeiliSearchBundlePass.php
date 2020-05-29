<?php

declare(strict_types=1);

namespace MeiliSearchBundle\DependencyInjection;

use MeiliSearchBundle\DataCollector\MeiliSearchBundleDataCollector;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Guillaume Loulier <contact@guillaumeloulier.fr>
 */
final class MeiliSearchBundlePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->registerTraceableClient($container);
        $this->registerDataCollector($container);
    }

    private function registerTraceableClient(ContainerBuilder $container): void
    {
    }

    private function registerDataCollector(ContainerBuilder $container): void
    {
        $dataCollector = (new Definition(MeiliSearchBundleDataCollector::class))
            ->setArguments([
                new Reference('meili_search.client.inner', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
            ])
            ->addTag('data_collector', [
                'template' => 'data_collector.html.twig',
                'id'       => 'app.request_collector',
            ])
        ;

        $container->setDefinition('meili_search.data_collector', $dataCollector);
    }
}
