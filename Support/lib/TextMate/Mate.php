<?php

declare(strict_types=1);

namespace TextMate;

class Mate {
	private string $path;

	public function __construct(string $path) {
		$this->path = $path;
	}

	public function open(string $file): void {
		shell_exec(sprintf(
			'%s %s',
			escapeshellarg($this->path),
			escapeshellarg($file),
		));
	}
}
