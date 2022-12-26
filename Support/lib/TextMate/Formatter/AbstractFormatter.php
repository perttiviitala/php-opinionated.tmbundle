<?php

declare(strict_types=1);

namespace TextMate\Formatter;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Preg;

abstract class AbstractFormatter extends AbstractFixer {
	public function getName(): string {
		$name = Preg::replace('/(?<!^)(?=[A-Z])/', '_', substr(static::class, 19, -5));

		return 'TextMate/'.strtolower($name);
	}
}
