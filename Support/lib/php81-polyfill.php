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
