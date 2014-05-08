clean:
	rm -rf vendor

test:
	./vendor/bin/phpcs --standard=psr2 src/
	./vendor/bin/phpcs --standard=psr2 tests/
	./vendor/bin/phpunit
