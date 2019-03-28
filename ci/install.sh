#!/bin/bash

main() {
	swoole_ver="4.2.13"
	hiredis_ver="0.14.0"
	stage=$(mktemp -d)
	cd $stage

	# Install hiredis
	wget https://github.com/redis/hiredis/archive/v${hiredis_ver}.tar.gz
	tar -zxf v${hiredis_ver}.tar.gz
	cd hiredis-${hiredis_ver}
	sudo make -j4
	sudo make install
	sudo ldconfig

	# Install swoole
	cd $stage
	wget https://github.com/swoole/swoole-src/archive/v${swoole_ver}.tar.gz
	tar -zxf v${swoole_ver}.tar.gz
	cd swoole-src-${swoole_ver}
	phpize
	./configure --enable-async-redis --enable-sockets=yes --enable-openssl=yes --enable-mysqlnd=yes
	sudo make -j4
	sudo make install
	echo "extension = swoole.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

	cd $TRAVIS_BUILD_DIR
	rm -rf $stage
}

main