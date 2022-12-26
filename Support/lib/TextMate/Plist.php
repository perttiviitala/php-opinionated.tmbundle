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

	private function encodeOpenStep(mixed $input): string {
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

	public function toDom(): \DOMDocument {
		$imp = new \DOMImplementation();
		$dom = $imp->createDocument(
			null,
			'plist',
			$imp->createDocumentType(
				'plist',
				'-//Apple//DTD PLIST 1.0//EN',
				'http://www.apple.com/DTDs/PropertyList-1.0.dtd',
			),
		);
		$dom->encoding = 'UTF-8';
		$dom->formatOutput = true;

		$version = $dom->createAttribute('version');
		$version->nodeValue = '1.0';
		$dom->documentElement->appendChild($version);

		$this->encodeXml($dom->documentElement, $this->data);

		return $dom;
	}

	private function encodeXml(\DOMNode $node, mixed $input): \DOMNode {
		switch (\gettype($input)) {
		case 'object':
			$dict = $node->ownerDocument->createElement('dict');
			foreach ($input as $key => $value) {
				/** @var int|string $key */
				$dict->appendChild($node->ownerDocument->createElement('key', (string) $key));
				$dict->appendChild($this->encodeXml($node, $value));
			}
			return $node->appendChild($dict);
		case 'array':
			/** @var array<int|string,mixed> $input */
			if (!array_is_list($input)) {
				return $this->encodeXml($node, (object) $input);
			}
			$array = $node->ownerDocument->createElement('array');
			foreach ($input as $value) {
				$array->appendChild($this->encodeXml($node, $value));
			}
			return $node->appendChild($array);
		case 'string':
			$string = $node->ownerDocument->createElement('string');
			$text = $node->ownerDocument->createTextNode($input);
			$string->appendChild($text);
			return $string;
		default:
			throw new \UnexpectedValueException(sprintf('Unknown value type %s', \gettype($input)));
		}
	}
}
