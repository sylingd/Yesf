<?php
use \PHPUnit\Framework\TestCase;
use \yesf\library\Loader;

class LoaderTest extends PHPUnit_Framework_TestCase {
	public function testPsr0() {
		Loader::addPsr0('Foo\\Bar', [YESF_TEST_DATA . 'Loader/Psr0']);
		$this->assertEquals(true, class_exists('Foo\\Bar\\Demo'));
		$this->assertEquals(true, class_exists('Foo\\Bar_Demo2'));
	}
	public function testPsr4() {
		Loader::addPsr4('Psr4\\Foo\\Bar\\', [YESF_TEST_DATA . 'Loader/Psr4']);
		$this->assertEquals(true, class_exists('Psr4\\Foo\\Bar\\Foo\\Bar\\Demo'));
	}
	public function testClassMap() {
		Loader::addClassMap([
			'Test1' => YESF_TEST_DATA . 'Loader/ClassMap/Test1.php',
			'Test2' => YESF_TEST_DATA . 'Loader/ClassMap/Test2.php'
		]);
		$this->assertEquals(true, class_exists('Test1'));
		$this->assertEquals(true, class_exists('Test2'));
	}
}