<?php

declare(strict_types=1);

require_once __DIR__.'/php80-polyfill.php';
require_once __DIR__.'/TextMate/errorhandler.php';

set_error_handler('TextMate\errorHandler');
set_exception_handler('TextMate\throwableHandler');
register_shutdown_function('TextMate\shutdownHandler');

spl_autoload_register(function (string $class): void {
	if (str_starts_with($class, 'TextMate')) {
		require sprintf('%s/%s.php', __DIR__, str_replace('\\', '/', $class));
	}
});
