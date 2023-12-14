<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__,
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->notPath('/vendor')
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
    ])
    ->setFinder($finder);
