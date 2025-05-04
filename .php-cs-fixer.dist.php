<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/workbench',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->exclude([
        'Support/Doubles/Fakes/Laravel/bootstrap',
        'Support/Doubles/Fakes/Laravel/storage',
    ]);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
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
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => true],
        'nullable_type_declaration' => ['syntax' => 'union'],
    ])
    ->setFinder($finder);
