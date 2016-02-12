ifndef MW_INSTALL_PATH
	MW_INSTALL_PATH=../../../..
endif

DIRS = api ffs insertables utils

default:
	php ${MW_INSTALL_PATH}/tests/phpunit/phpunit.php .

.PHONY: *Test.php $(DIRS)
*Test.php $(DIRS):
	php ${MW_INSTALL_PATH}/tests/phpunit/phpunit.php $@
