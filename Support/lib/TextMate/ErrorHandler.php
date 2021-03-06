<?php

declare(strict_types=1);

namespace TextMate;

class ErrorHandler {
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

		$backtrace = [];
		foreach ($trace as $index => $trace) {
			$method = isset($trace['class'])
				? $trace['class'].$trace['type'].$trace['function']
				: $trace['function'] ?? null;

			// these are boring as hell and do not aid
			if (!$file && (!$method || $method === '{closure}')) {
				continue;
			}

			$backtrace[] = sprintf(
				<<<HTML
				<tr>
					<td><a class="near" href="txmt://open?url=file://%s&line=%d">%s</a></td>
					<td>in <strong>%s</strong> at line %d</td>
				</tr>
				HTML,
				htmlentities($file),
				$line,
				htmlentities($method),
				htmlentities($file ? $trimPath($file) : 'unknown'),
				$line,
			);

			// these are overwritten by purpose to offset file/line for humans
			$file = $trace['file'] ?? $file;
			$line = $trace['line'] ?? $line;
		}

		$backtrace = sprintf(
			<<<HTML
			<blockquote>
				<table border="0" cellspacing="4" cellpadding="0">
					%s
				</table>
			</blockquote>
			HTML,
			implode("\n", $backtrace),
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
			exit(1);
		}
	}
}
