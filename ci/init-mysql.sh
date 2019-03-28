#!/bin/bash

main() {
	sudo mysql -e 'CREATE DATABASE est;'
	sudo mysql -e "use mysql; update user set authentication_string=PASSWORD('123456') where User='root'; update user set plugin='mysql_native_password';FLUSH PRIVILEGES;"
	sudo mysql_upgrade -u root -p123456
	sudo service mysql restart
	mysqlimport -f -uroot -p123456 --local test $TRAVIS_BUILD_DIR/tests/TestData/mysql.sql
}

main