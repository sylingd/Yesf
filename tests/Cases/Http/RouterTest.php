<?php
namespace YesfTest\Http;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Http\Router;
use Yesf\Http\Request;
use YesfApp\Http\FakeRequest;

class RouterTest extends TestCase {
	private $req;
	private $req_content;
	private $router;
	public function setUp() {
		$this->router = new Router;
		$this->req_content = new FakeRequest();
		$this->req = new Request($this->req_content);
	}
	public function testMap() {
		$this->req_content->server['request_uri'] = 'ap/foo';
		$this->router->parse($this->req);
		$this->assertEquals('ap', $this->req->controller);
		$this->assertEquals('foo', $this->req->action);
		$this->req_content->server['request_uri'] = 'ap/foo/bar';
		$this->router->parse($this->req);
		$this->assertEquals('ap', $this->req->module);
		$this->assertEquals('foo', $this->req->controller);
		$this->assertEquals('bar', $this->req->action);
	}
	public function testRewrite() {
		$this->req_content->server['request_uri'] = 'controller/this-is-A/this-is-B/key-1/val-1/key-2/val-2';
		$rule = 'controller/:paramA/:paramB/*';
		$dispatch = ['controller' => 'SimpleController', 'action' => 'SimpleAction'];
		$this->router->addRewrite($rule, $dispatch);
		$this->router->parse($this->req);
		$this->assertSame([
			'paramA' => 'this-is-A',
			'paramB' => 'this-is-B',
			'key-1' => 'val-1',
			'key-2' => 'val-2'
		], $this->req->param);
		$this->assertSame($dispatch['controller'], $this->req->controller);
		$this->assertSame($dispatch['action'], $this->req->action);
		$this->req_content->server['request_uri'] = 'invalid_url/test/demo';
		$this->router->parse($this->req);
		$this->assertEquals('invalid_url', $this->req->module);
		$this->assertEquals('test', $this->req->controller);
		$this->assertEquals('demo', $this->req->action);
	}
	public function testRegex() {
		$this->req_content->server['request_uri'] = 'thread-view-123-2.html';
		$dispatch = ['controller' => 'SimpleController', 'action' => 'SimpleAction'];
		$param = [
			1 => 'id',
			2 => 'page'
		];
		$this->router->addRegex('/^thread-view-([0-9]+)-([0-9]+)\\.html$/', $dispatch, $param);
		$this->router->parse($this->req);
		$this->assertSame([
			'id' => '123',
			'page' => '2'
		], $this->req->param);
		$this->assertSame($dispatch['controller'], $this->req->controller);
		$this->assertSame($dispatch['action'], $this->req->action);
	}
}