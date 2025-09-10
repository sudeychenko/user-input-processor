<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\RuleSet\RuleSets;

$finder = Finder::create()
    ->in('./src/')
    ->in('./tests/')
    ->append(['.php-cs-fixer.php']);

$ruleSet = new RuleSet();
$rules = array_reduce(
    RuleSets::getSetDefinitionNames(),
    static function (array $carry, string $ruleSetName): array {
        $carry[$ruleSetName] = true;

        return $carry;
    },
    [],
);

unset($rules['@PER'], $rules['@PER:risky']);

$rules['blank_line_before_statement'] = [
    'statements' => [
        'break',
        'case',
        'continue',
        'default',
        'exit',
        'for',
        'foreach',
        'if',
        'return',
        'switch',
        'throw',
        'try',
        'while',
        'yield',
    ],
];

$rules['class_attributes_separation'] = [
    'elements' => [
        'method' => 'one',
        'property' => 'one',
    ],
];

$rules['global_namespace_import'] = true;
$rules['linebreak_after_opening_tag'] = true;
$rules['mb_str_functions'] = true;

$rules['php_unit_test_case_static_method_calls'] = [
    'call_type' => 'this',
];

$rules['ordered_class_elements'] = [
    'sort_algorithm' => 'alpha',
    'order' => [
        'use_trait',

        'constant',
        'constant_public',
        'constant_protected',
        'constant_private',

        'property_static',
        'property_public_static',
        'property',
        'property_public',
        'property_protected_static',
        'property_protected',
        'property_private_static',
        'property_private',

        'construct',

        'method_public_static',
        'method',
        'method_public',
        'method_protected_static',
        'method_protected',
        'method_private_static',
        'method_private',

        'magic',
        'destruct',
        'phpunit',
    ],
];

$rules['phpdoc_line_span'] = [
    'property' => 'single',
];

$rules['single_line_throw'] = false;
$rules['static_lambda'] = true;
$rules['use_arrow_functions'] = true;

$rules['ordered_imports'] = [
    'imports_order' => ['class', 'function', 'const'],
];

$rules['phpdoc_to_comment'] = [
    'ignored_tags' => ['psalm-suppress'],
];

$rules['trailing_comma_in_multiline'] = [
    'elements' => ['arrays', 'arguments', 'parameters'],
];

$rules['return_assignment'] = false;

$rules['fopen_flags'] = ['b_mode' => true];

$rules['single_line_empty_body'] = false;

$rules['phpdoc_align'] = [
    'align' => 'left',
    'tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var', 'psalm-param'],
];

$rules['phpdoc_separation'] = [
    'groups' => [
        ['psalm-param'],
        ['psalm-return'],
        ['internal'],
        ['psalm-var'],
        ['psalm-import-type'],
        ['psalm-*'],
        ['template*'],
    ],
];

$rules['general_phpdoc_tag_rename'] = [
    'replacements' => [
        'var' => 'psalm-var',
        'param' => 'psalm-param',
        'return' => 'psalm-return',
        'api' => 'psalm-api',
    ],
];

$rules['phpdoc_order'] = [
    'order' => [
        'internal',
        'psalm-api',
        'psalm-param',
        'psalm-var',
        'psalm-return',
        'psalm-suppress',
        'psalm-readonly-allow-private-mutation',
    ],
];

$rules['php_unit_test_class_requires_covers'] = false;

// disable in favor of Prettier
$rules['concat_space'] = false;
$rules['function_declaration'] = false;
$rules['multiline_whitespace_before_semicolons'] = false;
$rules['operator_linebreak'] = false;
$rules['types_spaces'] = false;

return new Config()
    ->setRules($rules)
    ->setFinder($finder)
    ->setLineEnding("\n");
