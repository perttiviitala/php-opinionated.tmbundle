<?php

declare(strict_types=1);

namespace TextMate;

final class Context
{
	private static self $singleton;

	private function __construct(
		public readonly Mate $mate,
		public readonly Dialog $dialog,
	) {
	}

	public static function fromGlobals(): self
	{
		return self::$singleton ??= new self(
			new Mate($_SERVER['TM_MATE']),
			new Dialog($_SERVER['DIALOG']),
		);
	}
}
