encore: 
	./node_modules/.bin/encore dev

encore_watch:
	./node_modules/.bin/encore dev --watch

encore_production:
	./node_modules/.bin/encore production

phpcs:
	./vendor/bin/phpcs -p --encoding=utf-8 --standard=PSR2 --error-severity=1 src/
