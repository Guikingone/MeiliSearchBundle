<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude(__DIR__ . '/vendor')
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
    ])->setFinder($finder);
