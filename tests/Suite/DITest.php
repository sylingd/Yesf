<?php
use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\DI\Container;
use Yesf\Exception\NotFoundException;
use Yesf\Exception\InvalidClassException;
use Yesf\Exception\CyclicDependencyException;

class DITest extends TestCase {
	public function setUp() {
		Yesf::getLoader()->addPsr4('TestApp\\DI\\', YESF_TEST_DATA . '/DI');
		// Set alias
		Container::getInstance()->setAlias('ClassOneAlias', TestApp\DI\ClassOne::class);
	}
	public function testNotFoundError() {
		$this->expectException(NotFoundException::class);
		Container::getInstance()->get('A\\Not\\Exists\\Clazz');
	}
	public function testInvalidError() {
		$this->expectException(InvalidClassException::class);
		Container::getInstance()->get(TestApp\DI\InvalidClass::class);
	}
	public function testCyclicDependencyError() {
		$this->expectException(CyclicDependencyException::class);
		Container::getInstance()->get(TestApp\DI\CyclicOne::class);
	}
	public function testGet() {
		$clazz = Container::getInstance()->get(TestApp\DI\TestClass::class);
		$this->assertNull(TestApp\DI\TestClass::$static_one);
		$this->assertEquals('one', $clazz->getOneResult());
		$this->assertEquals('two', $clazz->getTwo()->getName());
		$this->assertEquals('one', $clazz->one_alias->getName());
		$this->assertEquals('one', $clazz->obj1_from_constructor->getName());
		$this->assertEquals('two', $clazz->obj2_from_constructor->getName());
		$this->assertNull($clazz->plain_var);
		$this->assertEquals('', $clazz->plain_var_with_type);
		$this->assertEquals(10, $clazz->plain_var_with_default);
	}
	public function testHas() {
		$this->assertTrue(Container::getInstance()->has('ClassOneAlias'));
		$this->assertTrue(Container::getInstance()->has(TestApp\DI\TestClass::class));
		$this->assertTrue(Container::getInstance()->has(TestApp\DI\UnloadedClass::class));
		$this->assertFalse(Container::getInstance()->has('A\\Not\\Exists\\Clazz'));
	}
	public function testNotSingleton() {
		Container::getInstance()->setNotSingleton(TestApp\DI\NotSingleton::class);
		$obj1 = Container::getInstance()->get(TestApp\DI\NotSingleton::class);
		$obj2 = Container::getInstance()->get(TestApp\DI\NotSingleton::class);
		$this->assertNotSame($obj1->id, $obj2->id);
	}
}