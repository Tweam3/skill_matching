<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'braces' => ['position_after_functions_and_oop_constructs' => 'next'],
        'cast_spaces' => true,
        'class_attributes_separation' => ['elements' => ['method' => 'one'], 'min' => 'one'],
        'concat_space' => ['spacing' => 'none'],
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        'trim_array_spaces' => true,
        'single_space_around_construct' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
