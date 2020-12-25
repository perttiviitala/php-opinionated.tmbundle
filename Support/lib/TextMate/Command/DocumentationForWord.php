<?php

declare(strict_types=1);

namespace TextMate\Command;

final class DocumentationForWord {
	// https://www.php.net/docs.php
	private const LANGUAGES = ['en', 'pt_BR', 'zh', 'fr', 'de', 'ja', 'ro', 'ru', 'es', 'tr'];

	// https://www.php.net/manual/en/langref.php
	// map order should follow the manual
	private const REFERENCES = [
		// Types
		'boolean' => ['language.types.boolean'],
		'integer' => ['language.types.integer'],
		'float' => ['language.types.float'],
		'string' => ['language.types.string'],
		'<<<' => ['language.types.string', 'heredoc'],
		'array' => ['language.types.array'],
		'iterable' => ['language.types.iterable'],
		'object' => ['language.types.object'],
		'resource' => ['language.types.resource'],
		'null' => ['language.types.null'],
		'callable' => ['language.types.callable'],
		'mixed' => ['language.types.declarations', 'mixed'],
		'void' => ['language.types.declarations', 'void'],
		'strict_types' => ['language.types.declarations', 'strict'],
		// Variables
		'global' => ['language.variables.scope', 'global'],
		// Constants
		'__LINE__' => ['language.constants.predefined'],
		'__FILE__' => ['language.constants.predefined'],
		'__DIR__' => ['language.constants.predefined'],
		'__FUNCTION__' => ['language.constants.predefined'],
		'__CLASS__' => ['language.constants.predefined'],
		'__TRAIT__' => ['language.constants.predefined'],
		'__METHOD__' => ['language.constants.predefined'],
		'__NAMESPACE__' => ['language.constants.predefined'],
		// Expressions
		// Operators
		// Operators → Arithmetic Operators
		'+' => ['language.operators.arithmetic'],
		'-' => ['language.operators.arithmetic'],
		'*' => ['language.operators.arithmetic'],
		'/' => ['language.operators.arithmetic'],
		'%' => ['language.operators.arithmetic'],
		'**' => ['language.operators.arithmetic'],
		// Operators → Assignment Operators
		// NOTE checked separately, any current word that ends with =
		// Operators → Bitwise Operators
		'&amp;' => ['language.operators.bitwise'],
		'|' => ['language.operators.bitwise'],
		'^' => ['language.operators.bitwise'],
		'~' => ['language.operators.bitwise'],
		'<<' => ['language.operators.bitwise'],
		'>>' => ['language.operators.bitwise'],
		// Operators → Comparison Operators
		'==' => ['language.operators.comparison'],
		'===' => ['language.operators.comparison'],
		'!=' => ['language.operators.comparison'],
		'<>' => ['language.operators.comparison'],
		'!==' => ['language.operators.comparison'],
		'<' => ['language.operators.comparison'],
		'> ' => ['language.operators.comparison'],
		'<=' => ['language.operators.comparison'],
		'>=' => ['language.operators.comparison'],
		'<=>' => ['language.operators.comparison'],
		'?:' => ['language.operators.comparison', 'ternary'],
		'??' => ['language.operators.comparison', 'coalesce'],
		// Operators → Error Control Operators
		'@' => ['language.operators.errorcontrol'],
		// Operators → Execution Operators
		'`' => ['language.operators.execution'],
		// Operators → Incrementing/Decrementing Operators
		'++' => ['language.operators.increment'],
		'--' => ['language.operators.increment'],
		// Operators → Logical Operators
		'and' => ['language.operators.logical'],
		'or' => ['language.operators.logical'],
		'xor' => ['language.operators.logical'],
		'!' => ['language.operators.logical'],
		'&amp;&amp;' => ['language.operators.logical'],
		'||' => ['language.operators.logical'],
		// Operators → String Operators
		'.' => ['language.operators.string'],
		// Operators → Array Operators
		// Operators → Type Operators
		'instanceof' => ['language.operators.type'],
		// Control Structures
		'if' => ['control-structures.if'],
		'else' => ['control-structures.else'],
		'elseif' => ['control-structures.elseif'],
		'while' => ['control-structures.while'],
		'for' => ['control-structures.for'],
		'foreach' => ['control-structures.foreach'],
		'break' => ['control-structures.break'],
		'continue' => ['control-structures.continue'],
		'switch' => ['control-structures.switch'],
		'case' => ['control-structures.switch'],
		'match' => ['control-structures.match'],
		'declare' => ['control-structures.declare'],
		'return' => ['function.return'],
		'require' => ['function.require'],
		'include' => ['function.include'],
		'require_once' => ['function.require-once'],
		'include_once' => ['function.include-once'],
		'goto' => ['control-structures.goto'],
		// Functions
		'function' => ['language.functions'],
		'fn' => ['functions.arrow'],
		// Classes and Objects
		// Classes and Objects → Basics
		'class' => ['language.oop5.basic', 'class'],
		'new' => ['language.oop5.basic', 'new'],
		'extends' => ['language.oop5.basic', 'extends'],
		'::class' => ['language.oop5.basic', 'class.class'],
		'?->' => ['language.oop5.basic', 'nullsafe'],
		// Classes and Objects → Properties
		// Classes and Objects → Class Constants
		'const' => ['language.oop5.constants'],
		// Classes and Objects → Autoloading Classes
		// Classes and Objects → Constructors and Destructors
		'__construct' => ['language.oop5.decon', 'constructor'],
		'__destruct' => ['language.oop5.decon', 'destructor'],
		// Classes and Objects → Visibility
		'public' => ['language.oop5.visibility'],
		'private' => ['language.oop5.visibility'],
		'protected' => ['language.oop5.visibility'],
		// Classes and Objects → Object Inheritance
		'parent' => ['language.oop5.interfaces'],
		'implements' => ['language.oop5.interfaces', 'implements'],
		// Classes and Objects → Scope Resolution Operator (::)
		'::' => ['language.oop5.paamayim.nekudotayim'],
		// Classes and Objects → Static Keyword
		'static' => ['language.oop5.static'],
		// Classes and Objects → Class Abstraction
		'abstract' => ['language.oop5.abstract'],
		// Classes and Objects → Object Interfaces
		'interface' => ['language.oop5.interfaces'],
		// Classes and Objects → Traits
		'trait' => ['language.oop5.traits'],
		// Classes and Objects → Anonymous classes
		// Classes and Objects → Overloading
		'__get' => ['language.oop5.overloading', 'members'],
		'__set' => ['language.oop5.overloading', 'members'],
		'__isset' => ['language.oop5.overloading', 'members'],
		'__unset' => ['language.oop5.overloading', 'members'],
		'__call' => ['language.oop5.overloading', 'methods'],
		'__callStatic' => ['language.oop5.overloading', 'methods'],
		// Classes and Objects → Object Iteration
		// Classes and Objects → Magic Methods
		'__sleep' => ['language.oop5.magic', 'sleep'],
		'__wakeup' => ['language.oop5.magic', 'sleep'],
		'__toString' => ['language.oop5.magic', 'tostring'],
		'__invoke' => ['language.oop5.magic', 'invoke'],
		'__set_state' => ['language.oop5.magic', 'set-state'],
		'__debugInfo' => ['language.oop5.magic', 'debuginfo'],
		// Classes and Objects → Final Keyword
		'final' => ['language.oop5.final'],
		// Classes and Objects → Object Cloning
		'clone' => ['language.oop5.cloning', 'clone'],
		'__clone' => ['language.oop5.cloning', 'clone'],
		// Classes and Objects → Comparing Objects
		// Classes and Objects → Late Static Bindings
		// Classes and Objects → Objects and references
		// Classes and Objects → Object Serialization
		// Classes and Objects → Covariance and Contravariance
		// Classes and Objects → OOP Changelog
		// Namespaces
		'namespace' => ['language.namespaces'],
		// Errors
		// Exceptions
		'try' => ['language.exceptions'],
		'throw' => ['language.exceptions'],
		'catch' => ['language.exceptions', 'catch'],
		'finally' => ['language.exceptions', 'finally'],
		// Generators
		'yield' => ['language.generators.syntax'],
		// Features
		// Command line usage
		// I/O streams
		'STDIN' => ['features.commandline.io-streams'],
		'STDOUT' => ['features.commandline.io-streams'],
		'STDERR' => ['features.commandline.io-streams'],
	];

	private static function locale(): string {
		if (class_exists(\Locale::class, false)) {
			foreach ([\Locale::getDefault(), strstr(\Locale::getDefault(), '_', true)] as $locale) {
				if (\in_array($locale, self::LANGUAGES)) {
					return $locale;
				}
			}
		}

		return self::LANGUAGES[0];
	}

	private static function formatManualUrl(string $page, ?string $anchor = null): string {
		if ($anchor) {
			// ancor repeats the page name ('foo', 'bar') => 'foo.php#foo.bar'
			return sprintf('https://www.php.net/manual/%s/%s.php#%2$s.%s', self::locale(), $page, $anchor);
		}

		return sprintf('https://www.php.net/manual/%s/%s.php', self::locale(), $page);
	}

	private static function formatUrl(string $page): string {
		return sprintf('https://www.php.net/%s/%s', self::locale(), $page);
	}

	public static function manualUrlForWord(string $word): ?string {
		if (!isset(self::REFERENCES[$word])) {
			// not a known keyword, use generic search page for built-in functions
			if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]+$/', $word)) {
				return self::formatUrl($word);
			}

			return null;
		}

		return self::formatManualUrl(...self::REFERENCES[$word]);
	}
}
