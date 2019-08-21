THIS_FILE := $(lastword $(MAKEFILE_LIST))

include .env

## ---------
##	Testing
## ---------

test_js: ## Run the JavaScript unit tests and watch
	./node_modules/.bin/jest --verbose

test_php: phpunit phpcs phpstan ## Run all PHP tests (PHPCS, PHPUnit, and PHPStan)

## ---------
##	Coding standards
## ---------

phpunit: ## Check that PHP unit tests pass
	./vendor/bin/phpunit tests/php

phpcs: ## Check that PHP complies with stylistic rules
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/php tests/php

phpstan: ## Check that PHP passes static analysis
	./vendor/bin/phpstan analyse src/php --level 6

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

create_migration: ## Create `[timestamp]_RENAME_ME.up.sql` and `[timestamp]_RENAME_ME.down.sql` in `database/migrations`
	migrate create -ext sql -dir database/migrations/ RENAME_ME
	find database/migrations -name "*_RENAME_ME.*.sql"

run_migrations: ## Run migrations
	migrate -database $(subst pgsql,postgres,$(DATABASE_URL))?sslmode=disable -path database/migrations up

## ---------
##	Environment
## ---------

webpack: ## Run a one-off webpack build
	./node_modules/.bin/webpack --watch --mode=development

sql: ## Run database setup
	psql -d hipper -a -f sql/init.sql

## ---------
##	Make setup
## ---------

_PHONY: help

.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(THIS_FILE) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

## --------
##  Docker
## --------

npmd: 
	docker-compose run node sh -c "cd /var/hipper; npm install"

webpackd:
	docker-compose run node sh -c "cd /var/hipper; ./node_modules/.bin/webpack --watch --mode=development"

sqlinitd:
	docker-compose run db sh -c "psql -U root -d hipper -a -f /var/sql/init.sql"

testjsd:
	docker-compose run node sh -c "cd /var/hipper; node_modules/.bin/jest --verbose"
