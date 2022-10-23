.PHONY: ${TARGETS}
.DEFAULT_GOAL := help

DIR := ${CURDIR}

help:
	@echo "\033[33mUsage:\033[0m"
	@echo "  make [command]"
	@echo ""
	@echo "\033[33mAvailable commands:\033[0m"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%s\033[0m___%s\n", $$1, $$2}' | column -ts___

install: ## Install project
	@composer install
	@composer --working-dir=tools/php-cs-fixer install
	@composer --working-dir=tools/phpstan install

cs-lint: ## Verify check styles
	@tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run -vvv

cs-fix: ## Apply Check styles
	@tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -vvv

phpstan: ## Run PHPStan
	@tools/phpstan/vendor/bin/phpstan analyse
