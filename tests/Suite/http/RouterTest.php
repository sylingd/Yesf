<?php
use \PHPUnit\Framework\TestCase;
use \yesf\library\http\Router;

class RouterTest extends PHPUnit_Framework_TestCase {
	public function testMap() {
		$uri = '/ap/foo';
		$result = ['controller' => 'ap', 'action' => 'foo'];
		$this->assertEquals($result, Router::parseMap($uri));
		$uri = '/ap/foo/bar';
		$result = ['module' => 'ap', 'controller' => 'foo', 'action' => 'bar'];
		$this->assertEquals($result, Router::parseMap($uri));
	}
	public function testRewrite() {
		$rule = 'controller/:paramA/:paramB/*';
		$uri = '/controller/this-is-A/this-is-B/key-1/val-1/key-2/val-2';
		$dispatch = ['controller' => 'SimpleController', 'action' => 'SimpleAction'];
		$result = [
			[
				'paramA' => 'this-is-A',
				'paramB' => 'this-is-B',
				'key-1' => 'val-1',
				'key-2' => 'val-2'
			], $dispatch
		];
		Router::addRewrite($rule, $dispatch);
		$this->assertEquals($result, Router::parseRewrite($uri));
	}
	public function testRegex() {
		$rule = '/^thread-view-([0-9]+)-([0-9]+)\\.html$/';
		$uri = 'thread-view-123-2.html';
		$dispatch = ['controller' => 'SimpleController', 'action' => 'SimpleAction'];
		$param = [
			1 => 'id',
			2 => 'page'
		];
		$result = [
			[
				'id' => '123',
				'page' => '2'
			], $dispatch
		];
		Router::addRegex($rule, $dispatch, $param);
		$this->assertEquals($result, Router::parseRegex($uri));
	}
}