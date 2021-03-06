<?php

declare(strict_types=1);

// assuming php to be version 7.3 (or slightly older)
require_once __DIR__.'/php80-polyfill.php';

// default settings on macOS does not report notices
error_reporting(\E_ALL);

// resolves class TextMate/Bar into $TM_BUNDLE_SUPPORT/Support/lib/TextMate/Bar.php
spl_autoload_register(function (string $class): void {
	// being a polite and only handling our own namespace
	if (str_starts_with($class, 'TextMate\\')) {
		// autoloaders generally should use absolute paths and avoid *_once -functions
		require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
	}
});

set_error_handler(function (int $errno, string $errstr, ?string $errfile, ?int $errline): bool {
	// respect configuration error levels
	if (!(error_reporting() & $errno)) {
		return false;
	}

	$type = 'Error';
	foreach (get_defined_constants(true)['Core'] as $key => $value) {
		if (strncmp($key, 'E_', 2) === 0 && $value === $errno) {
			// E_USER_ERROR -> User Error
			$type = ucwords(str_replace('_', ' ', strtolower(substr($key, 2))));
			break;
		}
	}

	$trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
	// remove our internal call to this handler
	array_shift($trace);
	// remove duplicate trigger_error call from the stack
	if (($trace[0]['function'] ?? null) == 'trigger_error') {
		array_shift($trace);
	}

	(new TextMate\ErrorHandler())->report(
		$type,
		$errstr,
		$errfile,
		$errline,
		$trace
	);

	return true;
});

set_exception_handler(function (Throwable $throwable): void {
	(new TextMate\ErrorHandler())->report(
		get_class($throwable),
		$throwable->getMessage(),
		$throwable->getFile(),
		$throwable->getLine(),
		$throwable->getTrace()
	);
	// any uncaught throwable will halt execution but exit with 0
	exit(1);
});

register_shutdown_function(function (): void {
	$error = error_get_last();
	if (!$error) {
		return;
	}
	// respect configuration error levels
	if (!(error_reporting() & $error['type'])) {
		return;
	}
	(new TextMate\ErrorHandler())->report(
		'Shutdown error',
		$error['message'],
		$error['file'],
		$error['line'],
		debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS)
	);
});
