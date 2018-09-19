## ---------
##	Testing
## ---------

test_js: ## Run the JavaScript unit tests and watch
	karma start

test_js_single_run: ## Single run for JavaScript unit tests
	karma start --single-run

## ---------
##	Coding standards
## ---------

phpcs: ## Check that PHP complies with stylistic rules
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/php

tslint:
	./node_modules/.bin/tslint -p tsconfig.json -c tslint.json

## ---------
##	Dependancies
## ---------

install_dependencies: ## Install composer and npm packages
	npm install && composer install

npm_install:
	npm install

composer_install:
	composer install

## ---------
##	Environment
## ---------

server: ## Run the dev server
	php -S 127.0.0.1:8000 -t public-roots/app/public

run: ## Run webpack once and start the dev server
	./node_modules/.bin/webpack --mode=development && php -S 127.0.0.1:8000 -t public-roots/app/public

webpack: ## Run a one-off webpack build
	./node_modules/.bin/webpack --watch --mode=development

sql:
	psql -d hleo -a -f sql/init.sql

## ---------
##	Make setup
## ---------

_PHONY: help

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
