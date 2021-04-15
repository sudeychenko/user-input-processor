<?php

declare(strict_types=1);

use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\RuleSet\RuleSets;

$finder = PhpCsFixer\Finder::create()
    ->in('./src/')
    ->in('./tests/')
    ->append(['.php_cs'])
;

$ruleSet = new RuleSet();

$rules = array_reduce(RuleSets::getSetDefinitionNames(), static function (array $carry, string $ruleSetName): array {
    $carry[$ruleSetName] = true;

    return $carry;
}, []);

$rules['blank_line_before_statement'] = [
    'statements' => ['break', 'case', 'continue', 'default', 'die', 'exit', 'for', 'foreach', 'if', 'return', 'switch', 'throw', 'try', 'while', 'yield'],
];

$rules['class_attributes_separation'] = [
    'elements' => ['method', 'property'],
];

$rules['concat_space'] = [
    'spacing' => 'one',
];

$rules['final_static_access'] = true;
$rules['global_namespace_import'] = true;
$rules['linebreak_after_opening_tag'] = true;
$rules['mb_str_functions'] = true;

$rules['ordered_class_elements'] = [
    'sortAlgorithm' => 'alpha',
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

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder($finder)
;
