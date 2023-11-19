<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(phpVersion: PhpVersion::PHP_72);
    $rectorConfig->importNames();
    $rectorConfig->disableParallel();
    $rectorConfig->importShortClasses();
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);
    $rectorConfig->autoloadPaths(autoloadPaths: [
        __DIR__ . '/vendor/autoload.php',
    ]);
    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/src/Test',
    ]);

    $rectorConfig->sets([
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::INSTANCEOF,
        SetList::EARLY_RETURN,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_81,
    ]);

    $rectorConfig->skip(criteria: [
        SimplifyBoolIdenticalTrueRector::class,
        SimplifyEmptyArrayCheckRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        CountArrayToEmptyArrayComparisonRector::class,
        DisallowedEmptyRuleFixerRector::class,
        ChangeNestedIfsToEarlyReturnRector::class,
    ]);
};
