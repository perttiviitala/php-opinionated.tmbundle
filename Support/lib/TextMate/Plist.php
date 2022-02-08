<?php

declare(strict_types=1);

namespace TextMate;

class Plist {
	/** @var array<int|string, mixed> */
	public array $data;

	/** @param array<int|string, mixed> $input */
	public function __construct(array $input = null) {
		$this->data = $input ?? [];
	}

	public function toJson(): string {
		return $this->encodeJson($this->data);
	}

	/** @param mixed $input */
	private function encodeJson($input): string {
		switch (\gettype($input)) {
		case 'array':
			/** @var array<mixed, mixed> $input */
			if (array_is_list($input)) {
				$pieces = [];
				foreach ($input as $value) {
					$pieces[] = $this->encodeJson($value);
				}
				return sprintf('( %s )', implode(', ', $pieces));
			}

			$pieces = [];
			foreach ($input as $key => $value) {
				$pieces[] = sprintf('%s = %s', $key, $this->encodeJson($value));
			}
			return sprintf('{ %s; }', implode('; ', $pieces));
		case 'string':
			/** @var string $input */
			return sprintf('"%s"', $input);
		case 'int':
			/** @var int $input */
			return sprintf('%d', $input);
		default:
			throw new \UnexpectedValueException(sprintf('Unknown value type %s', \gettype($input)));
		}
	}
}
