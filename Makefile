.DEFAULT_GOAL := help

# ==================================
# SCSSのコンパイル＋自動リロードコマンド
#
# 【使い方】
#   make dev type=style    → style.scss をコンパイル監視
#   make dev type=include  → include.scss をコンパイル監視
#
# 【実行される処理】
#   1. sass --watch
#        assets/scss/<type>.scss を監視し、
#        変更があるたびに assets/css/<type>.css へコンパイル
#
#   2. browser-sync
#        $(PROXY) のサーバーにプロキシ接続し、
#        以下のファイル変更を検知して自動リロード
#          - assets/css/*.css
#          - assets/js/*.js
#          - **/*.php
#
# 【PROXY について】
#   WordPressの「ホームURL」を wp コマンドで自動取得しています。
#   wp コマンドが使えない場合は以下のように直接指定してください:
#     PROXY := localhost:8888
#     PROXY := https://example.com
# ==================================

# WordPressのサイトURLをそのままプロキシホストとして使用する
# wp コマンドが PATH に通っていない場合はエラーになるため固定値に切り替えてください
PROXY := $(shell wp option get home --skip-plugins --skip-themes --quiet)
# PROXY := localhost:8888

.PHONY: dev
dev: ## SCSSコンパイル監視 + ブラウザ自動リロード（例: make dev type=icnlude）
	@test -n "$(type)" || (echo "Error: type required [ make dev type=style | make dev type=include ]" && exit 1)
	npx concurrently \
		"npx sass --watch assets/scss/$(type).scss:assets/css/$(type).css" \
		"npx browser-sync start --proxy '$(PROXY)' --files 'assets/css/*.css,assets/js/*.js,**/*.php'"

# Xserverで使用するPHPバイナリのバージョン
# PHPのバージョンを変更したい場合はここを書き換えてください
PHP := php8.2

.PHONY: composer
composer: ## Composerコマンドを実行する（例: make composer c="install"）
	@test -n "$(c)" || (echo "Error: command required [ 例: make composer c=\"install\" ]" && exit 1)
	$(PHP) /usr/bin/composer $(c)

.PHONY: stan
stan: ## PHPStanで静的解析を実行
	$(PHP) vendor/bin/phpstan analyse -c phpstan.neon > storage/framework/phpstan/phpstan.log || true

.PHONY: rector
rector: ## Rectorでリファクタリング候補を確認（変更は行わない）
	$(PHP) vendor/bin/rector process --dry-run > storage/framework/rector/rector.log || true

.PHONY: rector-fix
rector-fix: ## Rectorでリファクタリングを実際に適用
	$(PHP) vendor/bin/rector process > storage/framework/rector/rector-fix.log || true

# ==================================
# ヘルプ表示
# ==================================

.PHONY: help
help: ## 使用可能なコマンド一覧を表示する
	@echo "Usage: make [target] [options]"
	@echo ""
	@echo "Targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'
