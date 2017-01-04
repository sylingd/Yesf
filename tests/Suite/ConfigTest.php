<?php
use \PHPUnit\Framework\TestCase;
use \yesf\library\Config;

class ConfigTest extends PHPUnit_Framework_TestCase {
	const AppName = 'MyApplication';
	const YafDir = '/web/yaconf';
	public function setUp() {
		//require('../init.php');
	}
	public function testAll() {
		if (extension_loaded('Yaconf')) {
			//copy ini file to yaconf dir
			file_put_contents(self::YafDir . '/' . self::AppName . '.ini', file_get_contents(YESF_TEST_DATA . 'config_sample.ini'));
			sleep(2);
			//test yaconf
			$config = new Config(Config::YACONF, self::AppName);
			$this->assertEquals('MyTest', $config->get('test'));
			$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
			$this->assertEquals(NULL, $config->get('a_null_key'));
		}
		//test php
		$config = new Config(YESF_TEST_DATA . 'config_sample.php', self::AppName);
		$this->assertEquals('MyTest', $config->get('test'));
		$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
		$this->assertEquals(NULL, $config->get('a_null_key'));
	}
}