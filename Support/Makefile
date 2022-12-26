define PHP_DOCUMENTATION_LANGUAGES
	require __DIR__."/lib/bootstrap.php";
	echo implode(" ", TextMate\Command\DocumentationForWord::LANGUAGES);
endef

LANGS   := $(shell php -r '${PHP_DOCUMENTATION_LANGUAGES}')
TARGETS := resources/completions.json

all: $(TARGETS)

clean:
	@rm -rf dev/docs

dev/docs:
	@mkdir -p dev/docs

${addprefix dev/docs/, ${LANGS}}: dev/docs
	@echo "Fetching $@"
	@mkdir -p $@
	@curl -s https://www.php.net/distributions/manual/php_manual_$(notdir $@).tar.gz \
		| tar -xz -C $@ --strip 1

resources/completions.json: dev/docs/en vendor/autoload.php
	@bin/generate-completions

vendor/autoload.php: composer.json composer.lock
	@composer install

dev/functions.json: dev/docs/en
	@php dev/generate-new.php

dev/php.tmLanguage.json:
	curl https://raw.githubusercontent.com/microsoft/vscode/main/extensions/php/syntaxes/php.tmLanguage.json > $@
