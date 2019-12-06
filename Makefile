phpstan:
	php ./vendor/bin/phpstan analyse -l 7 ./src

test:
	php ./vendor/bin/phpunit -c phpunit.xml ./tests/

docker-build:
	docker build -t soap4me:latest .

docker-attach:
	docker run --rm -it --name soap4me -v $(shell pwd):/app/ --entrypoint /bin/bash soap4me:latest