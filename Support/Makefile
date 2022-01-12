define PHP_DOCUMENTATION_LANGUAGES
	require __DIR__."/lib/bootstrap.php";
	echo implode(" ", TextMate\Command\DocumentationForWord::LANGUAGES);
endef

LANGS   := $(shell php -r '${PHP_DOCUMENTATION_LANGUAGES}')
TARGETS := resources/completions.json

all: $(TARGETS)

clean:
	@rm -rf dev/docs composer.phar

dev/docs:
	@mkdir -p dev/docs

${addprefix dev/docs/, ${LANGS}}: dev/docs
	@echo "Fetching $@"
	@mkdir -p $@
	@curl -s https://www.php.net/distributions/manual/php_manual_$(notdir $@).tar.gz \
		| tar -xz -C $@ --strip 1

resources/completions.json: dev/docs/en vendor/autoload.php
	@bin/generate-completions

composer.phar:
	@php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	@php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') exit(0); echo 'Installer corrupt' . PHP_EOL; unlink('composer-setup.php'); exit(1);"
	@php composer-setup.php --version=2.2.x && rm composer-setup.php

vendor/autoload.php: composer.phar
	@php composer.phar install
