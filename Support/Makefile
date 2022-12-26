define PHP_DOCUMENTATION_LANGUAGES
	require __DIR__."/lib/bootstrap.php";
	echo implode(" ", TextMate\Command\DocumentationForWord::LANGUAGES);
endef

LANGS   := $(shell php -r '${PHP_DOCUMENTATION_LANGUAGES}')
TARGETS := resources/completions.json resources/PHP.tmLanguage

all: $(TARGETS)

clean:
	@rm -rf vendor dev resources/PHP.tmLanguage

dev/docs:
	@mkdir -p dev/docs

${addprefix dev/docs/, ${LANGS}}: dev/docs
	@echo "Fetching $@"
	@mkdir -p $@
	@curl -s https://www.php.net/distributions/manual/php_manual_$(notdir $@).tar.gz \
		| tar -xz -C $@ --strip 1

dev/php.tmLanguage.json:
	@echo "Fetching php.tmLanguage.json"
	curl https://raw.githubusercontent.com/microsoft/vscode/main/extensions/php/syntaxes/php.tmLanguage.json > $@

resources/completions.json: dev/docs/en vendor/autoload.php
	@echo "Generating completions"
	@bin/generate-completions > $@

resources/PHP.tmLanguage: dev/docs/en dev/php.tmLanguage.json vendor/autoload.php
	@echo "Generating PHP.tmLanguage"
	@bin/generate-syntax > $@
	@echo "Installing generated syntax"
	@cp $@ ../Syntaxes/PHP.tmLanguage

vendor/autoload.php:
	@echo "Installing dependencies"
	@composer install
