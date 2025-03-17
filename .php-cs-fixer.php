<?php

return (new PhpCsFixer\Config())->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['default' => 'single_space'],
    'blank_line_before_statement' => [
        'statements' => ['return']
    ],
    'cast_spaces' => ['space' => 'single'],
    'class_attributes_separation' => [
        'elements' => ['method' => 'one']
    ],
    'concat_space' => ['spacing' => 'one'],
    'constant_case' => ['case' => 'lower'],
    'declare_equal_normalize' => ['space' => 'single'],
    'escape_implicit_backslashes' => ['double_quoted' => true],
    'function_declaration' => ['closure_function_spacing' => 'none'],
    'include' => true,
    'lowercase_cast' => true,
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'no_extra_blank_lines' => [
        'tokens' => [
            'extra',
            'throw',
            'use',
            'use_trait',
        ],
    ],
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_short_bool_cast' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_around_offset' => true,
    'no_trailing_comma_in_list_call' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_whitespace_before_comma_in_array' => true,
    'no_whitespace_in_blank_line' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'object_operator_without_whitespace' => true,
    'operator_linebreak' => ['position' => 'beginning'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'phpdoc_indent' => true,
    'phpdoc_align' => [
        'align' => 'vertical',
        'tags' => ['param', 'return', 'throws', 'type', 'var'],
    ],
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_scalar' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_to_comment' => true,
    'phpdoc_trim' => true,
    'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
    'single_class_element_per_statement' => [
        'elements' => ['property']
    ],
    'single_import_per_statement' => true,
    'single_line_after_imports' => true,
    'single_quote' => true,
    'space_after_semicolon' => true,
    'standardize_not_equals' => true,
    'strict_comparison' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays'],
    ],
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'visibility_required' => ['elements' => ['property', 'method', 'const']],
    'whitespace_after_comma_in_array' => true,
])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                __DIR__ . '/src',
                __DIR__ . '/tests',
            ])
            ->name(patterns: '*.php')
            ->exclude(dirs: 'vendor')
    )
    ->setRiskyAllowed(isRiskyAllowed: true);

