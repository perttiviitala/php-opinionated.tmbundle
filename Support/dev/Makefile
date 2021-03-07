define PHP_DOCUMENTATION_LANGUAGES
	require __DIR__."/../lib/bootstrap.php";
	echo implode(" ", TextMate\Command\DocumentationForWord::LANGUAGES);
endef

LANGS   := $(shell php -r '${PHP_DOCUMENTATION_LANGUAGES}')
TARGETS := completions

all: $(TARGETS)

clean:
	@rm -rf docs

docs:
	@mkdir -p docs

${addprefix docs/, ${LANGS}}: docs
	@echo "Fetching $@"
	@mkdir -p $@
	@curl -s https://www.php.net/distributions/manual/php_manual_$(notdir $@).tar.gz \
		| tar -xz -C $@ --strip 1

completions: docs/en
	@echo "TODO generate completions from docs"
