<?php

declare(strict_types=1);

// FIXME Manual require should not be needed?
require_once __DIR__.'/lib/TextMate/Formatter/AbstractFormatter.php';
require_once __DIR__.'/lib/TextMate/Formatter/SwitchIndentFixer.php';

return (new PhpCsFixer\Config())
	->setIndent("\t")
	->registerCustomFixers([
		new TextMate\Formatter\SwitchIndentFixer(),
	])
	->setRules([
		'@Symfony' => true,
		'@Symfony:risky' => true,
		// Opinionated parts.
		'yoda_style' => [
			'equal' => false,
			'identical' => false,
			'less_and_greater' => false,
		],
		'curly_braces_position' => [
			'functions_opening_brace' => 'same_line',
		],
		'blank_line_before_statement' => ['statements' => []],
		'phpdoc_to_comment' => false,
		'TextMate/switch_indent' => true,
	])
	->setRiskyAllowed(true)
	->setFinder(
		PhpCsFixer\Finder::create()
			->exclude('vendor')
			->in(__DIR__)
	)
;
