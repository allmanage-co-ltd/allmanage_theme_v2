.DEFAULT_GOAL := help

.PHONY: help
help: ## Show this help
	@echo "Usage: make [target] [options]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

.PHONY: i
i: ##
	@composer install

.PHONY: php82
php82: ##
	@test -n "$(c)" || (echo "Error: command required" && exit 1)
	php8.2 /usr/bin/$(c)

.PHONY: gen
gen: ## Generate class file ( make gen dir=Hook name=Test )
	@test -n "$(dir)" || (echo "Error: dir required" && exit 1)
	@test -n "$(name)" || (echo "Error: name required" && exit 1)
	@mkdir -p app/$(dir)s
	@echo "<?php" > app/$(dir)s/$(name).php
	@echo "" >> app/$(dir)s/$(name).php
	@echo "namespace App\\$(dir)s;" >> app/$(dir)s/$(name).php
	@echo "" >> app/$(dir)s/$(name).php
	@echo "/**---------------------------------------------" >> app/$(dir)s/$(name).php
	@echo "* $(name)" >> app/$(dir)s/$(name).php" >> app/$(dir)s/$(name).php
	@echo "* ---------------------------------------------" >> app/$(dir)s/$(name).php
	@echo "*" >> app/$(dir)s/$(name).php
	@echo "*" >> app/$(dir)s/$(name).php
	@echo "*" >> app/$(dir)s/$(name).php
	@echo "*/" >> app/$(dir)s/$(name).php
	@echo "class $(name) extends $(dir)" >> app/$(dir)s/$(name).php
	@echo "{" >> app/$(dir)s/$(name).php
	@echo "  public function __construct() {}" >> app/$(dir)s/$(name).php
	@echo "" >> app/$(dir)s/$(name).php
	@echo "  /**" >> app/$(dir)s/$(name).php
	@echo "   *" >> app/$(dir)s/$(name).php
	@echo "   */" >> app/$(dir)s/$(name).php
	@echo "  public function boot(): void" >> app/$(dir)s/$(name).php
	@echo "  {" >> app/$(dir)s/$(name).php
	@echo "    //" >> app/$(dir)s/$(name).php
	@echo "  }" >> app/$(dir)s/$(name).php
	@echo "}" >> app/$(dir)s/$(name).php
	@echo "✓ Created app/$(dir)s/$(name).php"