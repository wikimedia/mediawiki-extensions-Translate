ifndef MW_INSTALL_PATH
	MW_INSTALL_PATH=../../../..
endif

TARGETS = api/ ffs/ messagegroups/ tag/ unit/ utils/ [A-Z]*/ *Test.php

default:
	php ${MW_INSTALL_PATH}/tests/phpunit/phpunit.php .

.PHONY: $(TARGETS)
$(TARGETS):
	php ${MW_INSTALL_PATH}/tests/phpunit/phpunit.php $@
