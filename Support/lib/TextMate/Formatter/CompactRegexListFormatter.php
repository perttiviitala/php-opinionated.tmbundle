<?php

declare(strict_types=1);

namespace TextMate\Formatter;

final class CompactRegexListFormatter {
	/**
	 * Generates compact regex that matches any string from list.
	 *
	 * Example:
	 * 	input  ["is_null", "is_int", "is_integer"]
	 * 	output "is_(null|int(eger)?)"
	 *
	 * @param string[] $list
	 */
	public function formatIterable(array $list): string {
		// Quote all inputs.
		$list = array_map(preg_quote(...), $list);
		sort($list);

		return sprintf('(?i)\b%s\b', self::getRegexpForList($list));
	}

	/** @param string[] $list */
	private function getRegexpForList(array $list): string {
		$buckets = [];
		$optional = false;
		foreach ($list as $string) {
			if (empty($string)) {
				$optional = true;
			} else {
				$buckets[mb_substr($string, 0, 1)][] = mb_substr($string, 1);
			}
		}
		if ($buckets) {
			$patterns = [];
			foreach ($buckets as $key => $value) {
				$patterns[] = $key.self::getRegexpForList($value);
			}
			if ($optional) {
				return '('.implode('|', $patterns).')?';
			} elseif (isset($patterns[1])) {
				return '('.implode('|', $patterns).')';
			} else {
				return $patterns[0];
			}
		}

		return '';
	}
}
