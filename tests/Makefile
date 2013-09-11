DIRS = api ffs insertables

default:
	php ../../../tests/phpunit/phpunit.php .

.PHONY: *Test.php $(DIRS)
*Test.php $(DIRS):
	php ../../../tests/phpunit/phpunit.php $@
