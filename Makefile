.PHONY: test make_clover

test:
	php vendor/bin/phpunit

test_clover:
	php vendor/bin/phpunit --coverage-clover build/logs/clover.xml
