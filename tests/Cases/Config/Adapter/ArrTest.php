<?php
namespace YesfTest\Config\Adapter;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Config\Adapter\Arr;

class ArrTest extends TestCase {
	public function testAll() {
		$env = Yesf::getEnvironment();
		Yesf::setEnvironment('gce');
		// Load
		$config = Arr::fromIniFile(APP_PATH . 'Config/TestFiles/config.ini');
		$this->assertEquals('gce', $config->get('connection.my.user'));
		$this->assertEquals('/product', $config->get('path2'));
		$this->assertEquals('/base', $config->get('path'));
		$this->assertEquals(['user' => 'gce', 'password' => 'gce'], $config->get('connection.my'));
		$this->assertEquals('localhost', $config->get('connection.my.host'));
		// Finish
		Yesf::setEnvironment($env);
	}
}