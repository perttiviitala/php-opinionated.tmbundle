<?php

declare(strict_types=1);

if (\PHP_VERSION_ID >= 80100) {
	return;
}

if (!function_exists('array_is_list')) {
	/** @param array<mixed, mixed> $array */
	function array_is_list(array $array): bool {
		$expectedKey = 0;
		foreach ($array as $i => $_) {
			if ($i !== $expectedKey) {
				return false;
			}
			++$expectedKey;
		}
		return true;
	}
}

if (!function_exists('enum_exists')) {
	function enum_exists(string $enum, bool $autoload = true): bool {
		if ($autoload) {
			spl_autoload_call($enum);
		}
		return false;
	}
}
