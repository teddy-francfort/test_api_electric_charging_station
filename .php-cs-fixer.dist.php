<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        'app',
        'config',
        'database',
        'routes',
        'tests'
        ]);

$config = new PhpCsFixer\Config();
return $config->setRiskyAllowed(true)->setRules([
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder);
