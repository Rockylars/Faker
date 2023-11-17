<?php

$finder = (new \PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests/Example',
        __DIR__ . '/tests/Fake',
        __DIR__ . '/tests/Unit',
    ])
    ->exclude([
        // The 'exclude' option does not seem to fully do its job when 'tests' is given in the 'in'.
        __DIR__ . '/tests/_output',
        __DIR__ . '/tests/_support/_generated'
    ])
;

return (new \PhpCsFixer\Config())
    /**
     * {@link https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst}
     * {@link https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/rules/index.rst}
     */
    ->setRules([
        // '@DoctrineAnnotation' => true,
        // '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'yoda_style' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_align' => ['align' => 'left'],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'increment_style' => ['style' => 'post'],
        'phpdoc_separation' => false,
        'single_line_throw' => false,
        'blank_line_before_statement' => false,
        'no_blank_lines_after_phpdoc' => false,
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder)
;
