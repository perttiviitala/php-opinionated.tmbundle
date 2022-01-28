<?php

declare(strict_types=1);

namespace TextMate;

class Plist {
	/** @param mixed $input */
	public function arrayToPlist($input): string {
		$output = '';
		switch (\gettype($input)) {
		case 'array':
			/** @var array<mixed, mixed> $input */
			if (array_is_list($input)) {
				$output .= '( ';
				$pieces = [];
				foreach ($input as $value) {
					$pieces[] = $this->arrayToPlist($value);
				}
				$output .= implode(', ', $pieces);
				$output .= ' )';
			} else {
				$output .= '{ ';
				$pieces = [];
				foreach ($input as $key => $value) {
					$pieces[] = sprintf('%s = %s', $key, $this->arrayToPlist($value));
				}
				$output .= implode('; ', $pieces);
				$output .= '; }';
			}
			break;
		case 'string':
			/** @var string $input */
			$output .= sprintf('"%s"', $input);
			break;
		case 'int':
			/** @var int $input */
			$output .= sprintf('%d', $input);
			break;
		default:
			$output .= '!'.\gettype($input).'!';
			break;
		}
		return $output;
	}
}
