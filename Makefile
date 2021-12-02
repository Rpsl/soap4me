phpstan:
	docker run --rm -it -v $(shell pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest -c 'composer update && composer install --prefer-dist --no-progress && ./vendor/bin/phpstan analyze --level max ./src ./tests'

test:
	docker run --rm -it -v $(shell pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest -c 'composer update && composer install --prefer-dist --no-progress && ./vendor/bin/phpunit -c .'

test-coverage:
	docker run --rm -it --name soap4me -v $(shell pwd):/app/ --entrypoint /usr/local/bin/phpdbg soap4me-dev:latest -qrr -d memory_limit=-1 vendor/bin/phpunit --configuration phpunit.xml --coverage-html /app/coverage/ && \
	rm -rf /app/build

build:
	docker build -t soap4me:latest .

attach:
	docker run --rm -it -v $(shell pwd):/app/ --name soap4me --entrypoint 'bash' soap4me:latest