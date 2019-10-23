phpstan:
	php ./vendor/bin/phpstan analyse -l 7 ./src

test:
	php ./vendor/bin/phpunit -c phpunit.xml ./tests/