<?php
namespace YesfTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Cache\Adapter\Yac as YesfYac;

class YacTest extends TestCase {
	public static $handler;
	public static function setUpBeforeClass() {
		self::$handler = new YesfYac();
	}
	public function testSingle() {
		$arr = [1, 2, 3];
		$key = uniqid();
		self::$handler->set($key, $arr);
		$this->assertSame($arr, self::$handler->get($key));
		$this->assertNull(self::$handler->get(uniqid()));
		$default = rand(1, 999);
		$this->assertSame($default, self::$handler->get(uniqid(), $default));
		self::$handler->delete($key);
		$this->assertNull(self::$handler->get($key));
	}
	public function testMulti() {
		$arr = [
			'key1' => 123,
			'key2' => "string",
			'key3' => [1, 2, 3]
		];
		self::$handler->setMultiple($arr);
		$this->assertSame($arr['key1'], self::$handler->get('key1'));
		$this->assertSame($arr['key2'], self::$handler->get('key2'));
		$this->assertSame($arr['key3'], self::$handler->get('key3'));
		$this->assertSame(array_values($arr), self::$handler->getMultiple(array_keys($arr)));
		$arr['key1'] = rand(1, 999);
		self::$handler->set('key1', $arr['key1']);
		$this->assertSame(array_values($arr), self::$handler->getMultiple(array_keys($arr)));
		$this->assertSame(
			[1, $arr['key1'], $arr['key2'], "str"],
			self::$handler->getMultiple(['not_exists', 'key1', 'key2', 'not_exists_2'],
				[1, 2, 3, "str"]));
		$this->assertSame(
			[0, $arr['key1'], 0],
			self::$handler->getMultiple(['not_exists', 'key1', 'not_exists_2'], 0));
	}
}