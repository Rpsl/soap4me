phpstan:
	php ./vendor/bin/phpstan analyse -l 7 ./src

test:
	php ./vendor/bin/phpunit -c phpunit.xml ./tests/

test-coverage:
	docker run --rm -it --name soap4me -v $(shell pwd):/app/ --entrypoint /usr/local/bin/phpdbg soap4me-dev:latest -qrr -d memory_limit=-1 vendor/bin/phpunit --configuration phpunit.xml --coverage-html /app/coverage/ && \
	rm -rf /app/build

docker-build:
	docker build -t soap4me:latest .

docker-build-dev:
	docker build -t soap4me-dev:latest . && \
	docker run --rm -it --name soap4me-dev -v $(shell pwd):/app/ --entrypoint /usr/local/bin/composer soap4me-dev:latest install --prefer-dist

docker-attach:
	docker run --rm -it --name soap4me -v $(shell pwd):/app/ --entrypoint /bin/bash soap4me-dev:latest