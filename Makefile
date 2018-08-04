encore: 
	./node_modules/.bin/encore dev

encore_watch:
	./node_modules/.bin/encore dev --watch

encore_production:
	./node_modules/.bin/encore production

phpcs:
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/

server:
	php -S 127.0.0.1:8000 -t public

test_js:
	karma start

reset_env:
	rm -rf node_modules && yarn install && composer install

run:
	./node_modules/.bin/encore dev && php -S 127.0.0.1:8000 -t public
