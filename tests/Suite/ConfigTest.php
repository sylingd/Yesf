<?php
namespace YesfTest;

use PHPUnit\Framework\TestCase;
use Yesf\Config;

/*
class ConfigTest extends TestCase {
	const AppName = 'MyApplication';
	const YafDir = '/web/yaconf';
	public function testAll() {
		if (extension_loaded('Yaconf')) {
			//copy ini file to yaconf dir
			file_put_contents(self::YafDir . '/' . self::AppName . '.ini', file_get_contents(TEST_APP . 'config_sample.ini'));
			sleep(2);
			//test yaconf
			$config = new Config(Config::YACONF, self::AppName);
			$this->assertEquals('MyTest', $config->get('test'));
			$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
			$this->assertEquals(null, $config->get('a_null_key'));
		}
		//test ini
		$config = new Config(TEST_APP . 'config_sample.ini', self::AppName);
		$this->assertEquals('utf-8', $config->get('application.charset'));
		$this->assertEquals('map', $config->get('application.router.type'));
		$this->assertEquals(null, $config->get('a_null_key'));
		//test php
		$config = new Config(require(TEST_APP . 'config_sample.php'), self::AppName);
		$this->assertEquals('MyTest', $config->get('test'));
		$this->assertEquals('127.0.0.1', $config->get('MySQL.host'));
		$this->assertEquals(null, $config->get('a_null_key'));
	}
}
*/