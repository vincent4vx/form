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

infection.phar:
	wget https://github.com/infection/infection/releases/download/0.26.15/infection.phar
	wget https://github.com/infection/infection/releases/download/0.26.15/infection.phar.asc
	gpg --recv-keys C6D76C329EBADE2FB9C458CFC5095986493B4AA0
	gpg --with-fingerprint --verify infection.phar.asc infection.phar
	chmod +x infection.phar

infection: infection.phar
	php -d memory_limit=2G ./infection.phar --threads=8
	rm -rf tests/_tmp tests/_tmp*

.PHONY: unit phpstan phpcs build bench
