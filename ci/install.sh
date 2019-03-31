#!/bin/bash

main() {
	swoole_ver="4.2.13"
	hiredis_ver="0.14.0"
	yac_ver="2.0.2"
	stage=$(mktemp -d)

	# Install hiredis
	cd $stage
	wget -O hiredis.tar.gz https://github.com/redis/hiredis/archive/v${hiredis_ver}.tar.gz
	tar -zxf hiredis.tar.gz
	cd hiredis-${hiredis_ver}
	make -j4
	sudo make install
	sudo ldconfig

	# Install swoole
	cd $stage
	wget -O swoole.tar.gz https://github.com/swoole/swoole-src/archive/v${swoole_ver}.tar.gz
	tar -zxf swoole.tar.gz
	cd swoole-src-${swoole_ver}
	phpize
	./configure --enable-sockets=yes --enable-openssl=yes --enable-mysqlnd=yes
	make -j4
	sudo make install
	echo "extension = swoole.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

	# Install Yac
	can_install_yac=$(php -r "echo version_compare(PHP_VERSION, '7.3');")
	if [[ "$can_install_yac" == "-1" ]]; then
		cd $stage
		wget -O yac.tar.gz https://github.com/laruence/yac/archive/yac-${yac_ver}.tar.gz
		tar -zxf yac.tar.gz
		cd yac-yac-${yac_ver}
		phpize
		./configure
		make -j4
		sudo make install
		echo "extension = yac.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
		echo "yac.enable = On" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
		echo "yac.enable_cli = On" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
	else
		echo -e "Skip install Yac\n"
	fi

	cd $TRAVIS_BUILD_DIR
	sudo rm -rf $stage
}

main