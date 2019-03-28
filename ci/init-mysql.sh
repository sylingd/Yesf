#!/bin/bash

main() {
	sudo mysql -e 'CREATE DATABASE IF NOT EXISTS test;'
	sudo mysql -e "USE mysql; UPDATE user SET password=PASSWORD('123456') where User='root'; FLUSH PRIVILEGES;"
	sudo service mysql restart
	mysqlimport -f -uroot -p123456 --local test $TRAVIS_BUILD_DIR/tests/TestData/mysql.sql
}

main
