unit:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan

phpcs:
	vendor/bin/php-cs-fixer fix --dry-run --diff

bench:
	vendor/bin/phpbench run --report=aggregate

build: unit phpstan phpcs

php:
	docker-compose -f docker-compose.yaml build
	docker-compose -f docker-compose.yaml run php bash

php81: export PHP_VERSION = 8.1
php81: php

php82: export PHP_VERSION = 8.2
php82: php

.PHONY: unit phpstan phpcs build bench
