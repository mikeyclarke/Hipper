THIS_FILE := $(lastword $(MAKEFILE_LIST))

include .env.local

## ---------
##	Testing
## ---------

test_ts: eslint jest ## Run all TypeScript tests (TSLint, and Jest)

test_php: phpunit phpcs phpstan ## Run all PHP tests (PHPCS, PHPUnit, and PHPStan)

## ---------
##	Unit tests
## ---------

jest: ## Check that JS unit tests pass
	./node_modules/.bin/jest --verbose

phpunit: ## Check that PHP unit tests pass
	./vendor/bin/phpunit tests/php

## ---------
##	Coding standards
## ---------

phpcs: ## Check that PHP complies with stylistic rules
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/php tests/php

eslint: ## Lint Typescript files
	./node_modules/.bin/eslint "src/ts/**"

## ---------
##	Static analysis
## ---------

phpstan: ## Check that PHP passes static analysis
	./vendor/bin/phpstan analyse src/php --level 6

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

create_migration: ## Create a new “up” and a new “down” migration file in database/migrations
	@read -p "Enter migration name (e.g. “create_team_table”): " migrationName; \
	migrate create -ext sql -dir database/migrations/ $$migrationName; \
	find database/migrations -name "*_$$migrationName.*.sql"

run_migrations: ## Run migrations
	migrate -database $(subst pgsql,postgres,$(DATABASE_URL))?sslmode=disable -path database/migrations up

## ---------
##	Webpack
## ---------

webpack: ## Run webpack in watch mode
	./node_modules/.bin/webpack --watch --config webpack.dev.js

webpack-production: ## Build webpack assets for production
	./node_modules/.bin/webpack --config webpack.production.js

## ---------
##	Production
## ---------

build: test_ts webpack-production ## Build Hipper for production

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
