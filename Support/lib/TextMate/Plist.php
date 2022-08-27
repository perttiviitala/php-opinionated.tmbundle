<?php

declare(strict_types=1);

namespace TextMate;

final class Plist {
	public function __construct(
		/** @var array<int|string, mixed> */
		public array $data = []
	) {
	}

	public function toOpenStep(): string {
		return $this->encodeOpenStep($this->data);
	}

	/** @param mixed $input */
	private function encodeOpenStep($input): string {
		switch (\gettype($input)) {
		case 'array':
			/** @var array<mixed, mixed> $input */
			if (array_is_list($input)) {
				$pieces = [];
				foreach ($input as $value) {
					$pieces[] = $this->encodeOpenStep($value);
				}
				return sprintf('( %s )', implode(', ', $pieces));
			}

			$pieces = [];
			foreach ($input as $key => $value) {
				$pieces[] = sprintf('%s = %s', $key, $this->encodeOpenStep($value));
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
