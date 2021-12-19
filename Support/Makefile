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

resources/completions.json: dev/docs/en
	@bin/generate-completions
