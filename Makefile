# This file is part of ffaker.phar project

INSTALL_DIR=/usr/local/bin

.SILENT:

program: build_all
	echo "Done. Run \`make install\` as root to install phar into your system"

install:
	cp build/*.phar $(INSTALL_DIR)
	echo "Installation complete"

uninstall:
	rm $(INSTALL_DIR)/ffaker.phar
	rm $(INSTALL_DIR)/ffaker-dump.phar

# run software tests
runtests:
	echo "Run phpunit"
	# перенаправить ошибки в dev/null
	cd test/ && \
	./vendor/bin/phpunit test/ 2> /dev/null;

	echo "Done"

# create test dir
test:
	rm -rf test
	mkdir test

	cp -R pre_build/* test/
	cp -R test_suite/* test/

	# install test dependencies
	cd test/ && \
	php composer.phar update;

clean:
	rm -rf pre_build
	rm -rf build/*
	rm -rf test

# build ffaker only
build_ffaker: prepare_php
	php build.php ffaker
	chmod +x build/ffaker.phar

# build ffaker-dumper only
build_ffaker_dumper: prepare_php
	php build.php ffaker-dump
	chmod +x build/ffaker-dump.phar

# build all ffaker components
build_all: prepare_php
	php build.php all
	chmod +x build/*.phar

# Preparing php scripts for phar building
prepare_php: prepare_build
	echo "Prepare PHP scripts . . ."
	# it was unstable, so i removed that for time
	# for file in $(shell find `pwd`/pre_build | grep .php\$$) ; do \
	# 	`cat $$file | sed -e /^#\!.usr.*php\$$/d | tee $$file > /dev/null` ; \
	# done

	# install composer into test dir
	cd pre_build/ && \
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
	php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); exit(1); }" && \
	php composer-setup.php && \
	php -r "unlink('composer-setup.php');";

	cd pre_build/ && ./composer.phar install;

# creates fresh pre_build dir
prepare_build:
	rm -rf build
	mkdir build
	echo "Creating pre_build directory for additional processing . . ."
	rm -rf pre_build
	mkdir pre_build
	cp -R src/* pre_build/