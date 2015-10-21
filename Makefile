.PHONY: test make_clover

test:
	php vendor/bin/phpunit

test_clover:
	php vendor/bin/phpunit --coverage-clover builds/logs/clover.xml
