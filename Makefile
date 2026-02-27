.DEFAULT_GOAL := help
PHP=php8.2

.PHONY: help
help: ## Show this help
	@echo "Usage: make [target] [options]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

.PHONY: composer
composer: ##
	@test -n "$(c)" || (echo "Error: command required" && exit 1)
	php8.2 /usr/bin/composer $(c)

.PHONY: cs
cs:
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run > storage/logs/php-cs-fixer.log || true

.PHONY: cs-fix
cs-fix:
	$(PHP) vendor/bin/php-cs-fixer fix > storage/logs/php-cs-fixer-fix.log || true

.PHONY: stan
stan:
	$(PHP) vendor/bin/phpstan analyse -c phpstan.neon > storage/logs/phpstan.log || true

.PHONY: rector
rector:
	$(PHP) vendor/bin/rector process --dry-run >  storage/logs/rector.log || true

.PHONY: rector-fix
rector:
	$(PHP) vendor/bin/rector process --dry-run >  storage/logs/rector-fix.log || true
