<?php

return (new PhpCsFixer\Config())
	->setIndent("\t")
	->setRules([
		'@Symfony' => true,
		'@Symfony:risky' => true,
		// Opinionated parts.
		'yoda_style' => [
			'equal' => false,
			'identical' => false,
			'less_and_greater' => false,
		],
		'braces' => [
			'allow_single_line_closure' => true,
			'position_after_functions_and_oop_constructs' => 'same',
		],
		'blank_line_before_statement' => [],
		'phpdoc_to_comment' => false,
	])
	->setFinder(
		PhpCsFixer\Finder::create()
			->exclude('vendor')
			->in(__DIR__)
	)
;
