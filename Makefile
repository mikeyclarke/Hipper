phpcs:
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/php

server:
	php -S 127.0.0.1:8000 -t public-roots/app/public

test_js:
	karma start

test_js_single_run:
	karma start --single-run

reset_env:
	rm -rf node_modules && yarn install && composer install

run:
	./node_modules/.bin/webpack --mode=development && php -S 127.0.0.1:8000 -t public-roots/app/public

webpack:
	./node_modules/.bin/webpack --watch --mode=development
