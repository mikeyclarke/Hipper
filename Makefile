## ---------
##	Testing
## ---------

test_js: ## Run the JavaScript unit tests and watch
	./node_modules/.bin/jest --verbose

test_php:
	./vendor/bin/phpunit tests/php

## ---------
##	Coding standards
## ---------

phpcs: ## Check that PHP complies with stylistic rules
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/php tests/php

tslint: ## Lint Typescript files
	./node_modules/.bin/tslint -p tsconfig.json -c tslint.json

## ---------
##	Dependancies
## ---------

install_dependencies: ## Install composer and npm packages
	npm install && composer install

npm_install: ## Install npm packages
	npm install

composer_install: ## Install composer packages
	composer install

## ---------
##	Environment
## ---------

webpack: ## Run a one-off webpack build
	./node_modules/.bin/webpack --watch --mode=development

sql: ## Run database setup
	psql -d hleo -a -f sql/init.sql

## ---------
##	Make setup
## ---------

_PHONY: help

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

## --------
##  Docker
## --------

npmd: 
	docker-compose run node sh -c "cd /var/hleo; npm install"

webpackd:
	docker-compose run node sh -c "cd /var/hleo; ./node_modules/.bin/webpack --watch --mode=development"

sqlinitd:
	docker-compose run db sh -c "psql -U root -d hleo -a -f /var/sql/init.sql"

testjsd:
	docker-compose run node sh -c "cd /var/hleo; node_modules/.bin/jest --verbose"
