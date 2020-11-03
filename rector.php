<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/vendor',
        __DIR__ . '/src/Test',
    ]);

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/vendor/autoload.php',
    ]);

    $parameters->set(Option::SETS, [
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::DEAD_CODE,
        //SetList::CODING_STYLE,
        //SetList::CODING_STYLE_ADVANCED,
        SetList::PERFORMANCE,
        //SetList::PHPUNIT_CODE_QUALITY,
        SetList::PHPSTAN,
        SetList::SOLID,
        SetList::TWIG_20,
        SetList::TWIG_UNDERSCORE_TO_NAMESPACE,
        SetList::TYPE_DECLARATION,
    ]);

    $parameters->set(Option::ENABLE_CACHE, true);
};
