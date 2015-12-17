<?php


$header = <<< EOF
This file is part of the Сáша framework.

(c) tchiotludo <http://github.com/tchiotludo>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . "/src")
    ->in(__DIR__ . '/tests');

$fixers = array(
  '-psr0',
  'extra_empty_lines',
  'header_comment',
  'no_empty_lines_after_phpdocs',
  'phpdoc_align',
  'phpdoc_indent',
  'phpdoc_inline_tag',
  'phpdoc_no_access',
  'phpdoc_no_package',
  'phpdoc_order',
  'phpdoc_scalar',
  'phpdoc_separation',
  'phpdoc_to_comment',
  'phpdoc_trim',
  'phpdoc_type_to_var',
  'phpdoc_types',
  'phpdoc_var_without_name',
  'return',
  'spaces_cast',
  'unalign_equals',
  'unused_use',
  'whitespacy_lines',
  'concat_with_spaces',
  'ordered_use',
  'single_quote',
  'short_array_syntax',
  'short_echo_tag',
);

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->fixers($fixers)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL);
