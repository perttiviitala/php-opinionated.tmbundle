<?php

declare(strict_types=1);

if (PHP_VERSION_ID >= 80000) {
	return;
}

if (!defined('FILTER_VALIDATE_BOOL') && defined('FILTER_VALIDATE_BOOLEAN')) {
	define('FILTER_VALIDATE_BOOL', FILTER_VALIDATE_BOOLEAN);
}

if (!function_exists('fdiv')) {
	function fdiv(float $dividend, float $divisor): float {
		return $dividend / $divisor;
	}
}

if (!function_exists('preg_last_error_msg')) {
	function preg_last_error_msg(): string {
		switch (preg_last_error()) {
		case PREG_INTERNAL_ERROR:
			return 'Internal error';
		case PREG_BAD_UTF8_ERROR:
			return 'Malformed UTF-8 characters, possibly incorrectly encoded';
		case PREG_BAD_UTF8_OFFSET_ERROR:
			return 'The offset did not correspond to the beginning of a valid UTF-8 code point';
		case PREG_BACKTRACK_LIMIT_ERROR:
			return 'Backtrack limit exhausted';
		case PREG_RECURSION_LIMIT_ERROR:
			return 'Recursion limit exhausted';
		case PREG_JIT_STACKLIMIT_ERROR:
			return 'JIT stack limit exhausted';
		case PREG_NO_ERROR:
			return 'No error';
		default:
			return 'Unknown error';
		}
	}
}

if (!function_exists('str_starts_with')) {
	function str_starts_with(string $haystack, string $needle): bool {
		return strncmp($haystack, $needle, strlen($needle)) === 0;
	}
}

if (!function_exists('str_ends_with')) {
	function str_ends_with(string $haystack, string $needle): bool {
		return substr_compare($haystack, $needle, -strlen($needle)) === 0;
	}
}

if (!function_exists('str_contains')) {
	function str_contains(string $haystack, string $needle): bool {
		return strpos($haystack, $needle) !== false;
	}
}

if (!function_exists('get_debug_type')) {
	function get_debug_type($value): string {
		if ($value === null) {
			return 'null';
		}
		if (is_bool($value)) {
			return 'bool';
		}
		if (is_string($value)) {
			return 'string';
		}
		if (is_array($value)) {
			return 'array';
		}
		if (is_int($value)) {
			return 'int';
		}
		if (is_float($value)) {
			return 'float';
		}
		if (is_object($value)) {
			$class = get_class($value);
			if (strpos($class, '@') === false) {
				return $class;
			}

			return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
		}
		if (is_resource($value)) {
			return sprintf('resource (%s)', get_resource_type($value));
		}

		return 'unknown';
	}
}

if (!function_exists('get_resource_id')) {
	function get_resource_id($res): int {
		if (!is_resource($res)) {
			throw new \TypeError(sprintf('Argument 1 passed to get_resource_id() must be of the type resource, %s given', get_debug_type($res)));
		}

		return (int) get_resource_type($res);
	}
}
