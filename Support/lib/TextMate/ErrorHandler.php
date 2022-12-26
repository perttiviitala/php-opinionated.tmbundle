<?php

declare(strict_types=1);

namespace TextMate;

class ErrorHandler {
	/** @param ?array<array<string, string>> $trace */
	public function report(string $type, string $message, ?string $file, ?int $line, ?array $trace = []): void {
		$trimPath = function ($path) {
			$paths = [];
			if (isset($_SERVER['TM_PROJECT_DIRECTORY'])) {
				$paths[] = $_SERVER['TM_PROJECT_DIRECTORY'];
			}
			if (isset($_SERVER['TM_DIRECTORY'])) {
				$paths[] = $_SERVER['TM_DIRECTORY'];
			}
			foreach ($paths as $base) {
				if (str_starts_with($path, $base)) {
					return substr($path, \strlen($base) + 1);
				}
			}

			return $path;
		};

		// add point of occurance to stack
		$trace[] = ['function' => '{main}', 'file' => $file, 'line' => $line];

		$tableRows = [];
		/** @var array{
		 *     function: ?string,
		 *     line: ?int,
		 *     file: ?string,
		 *     class: ?string,
		 *     object: ?object,
		 *     type: ?string,
		 *     args: ?array<mixed>,
		 * } $level */
		foreach ($trace as $index => $level) {
			$method = (isset($level['class'])
				? $level['class'].$level['type'].$level['function']
				: $level['function']) ?? '{unknown}';

			// these are boring as hell and do not aid
			if (!$file && (!$method || $method[0] == '{')) {
				continue;
			}

			$tableRows[] = sprintf(
				<<<HTML
				<tr>
					<td>%s</td>
					<td>in <strong>%s</strong> at line %d</td>
				</tr>
				HTML,
				$file
					? sprintf(<<<'HTML'
						<a class="near" href="txmt://open?url=file://%s&line=%d">%s</a>
						HTML,
						htmlentities($file),
						$line,
						htmlentities($trimPath($file)),
					)
					: 'unknown',
				htmlentities($method),
				$line,
			);

			// these are overwritten by purpose to offset file/line for humans
			$file = $level['file'] ?? $file;
			$line = $level['line'] ?? $line;
		}

		$backtrace = sprintf(
			<<<HTML
			<blockquote>
				<table border="0" cellspacing="4" cellpadding="0">
					%s
				</table>
			</blockquote>
			HTML,
			implode("\n", $tableRows),
		);

		$handle = null;
		fwrite(
			isset($_SERVER['TM_ERROR_FD'])
				? $handle = fopen(sprintf('php://fd/%d', $_SERVER['TM_ERROR_FD']), 'w')
				: \STDOUT,
			sprintf(
				<<<HTML
				<div id="exception_report" class="framed">
					<p id="exception">
						<strong>%s</strong>
						%s
					</p>
					%s
				</div>
				HTML,
				htmlentities($type),
				htmlentities($message),
				$backtrace,
			)
		);

		if ($handle) {
			fclose($handle);
		}

		// TextMate goes unresponsive if you try to write too many times to error file descriptor
		static $counter = 0;
		if (++$counter >= ($_SERVER['TM_PHP_ERROR_LIMIT'] ?? 5)) {
			trigger_error('Limiting errors to 5, stopping execution', \E_USER_ERROR);
		}
	}
}
