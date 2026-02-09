<?php

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__ . '/app')
  ->name('*.php')
  ->ignoreDotFiles(true)
  ->ignoreVCS(true);

return (new PhpCsFixer\Config())
  ->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'blank_line_after_opening_tag' => true,
    'blank_line_after_namespace' => true,
    'method_argument_space' => [
      'on_multiline' => 'ensure_fully_multiline',
    ],
    'single_quote' => true,
    'trailing_comma_in_multiline' => true,
  ])
  ->setFinder($finder)
  ->setRiskyAllowed(true);