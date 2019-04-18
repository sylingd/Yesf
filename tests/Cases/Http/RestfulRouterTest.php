<?php
namespace YesfTest\Http;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Http\RestfulRouter;
use Yesf\Http\Request;
use YesfApp\Http\FakeRequest;

class RestfulRouterTest extends TestCase {
	private $req;
	private $req_content;
	private $router;
	public function setUp() {
		$this->router = new RestfulRouter;
		$this->req_content = new FakeRequest();
		$this->req = new Request($this->req_content);
	}
	public function testParamCorrect() {
		$this->req_content->server['request_method'] = 'get';
		$this->req_content->server['request_uri'] = '/user/123';
		$this->router->get('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'view'
		], [
			'id' => '(\d+)'
		]);
		$this->router->parse($this->req);
		$this->assertEquals('index', $this->req->module);
		$this->assertEquals('user', $this->req->controller);
		$this->assertEquals('view', $this->req->action);
		$this->assertEquals('123', $this->req->param['id']);
	}
	public function testParamIncorrect() {
		$this->req_content->server['request_method'] = 'get';
		$this->req_content->server['request_uri'] = '/user/someone';
		$this->router->get('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'view'
		], [
			'id' => '(\d+)'
		]);
		$this->router->parse($this->req);
		$this->assertNull($this->req->module);
		$this->assertNull($this->req->controller);
		$this->assertNull($this->req->action);
	}
	public function testMethod() {
		$this->req_content->server['request_method'] = 'put';
		$this->req_content->server['request_uri'] = '/user/123';
		$this->router->get('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'view'
		]);
		$this->router->put('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'update'
		]);
		$this->router->parse($this->req);
		$this->assertEquals('update', $this->req->action);
	}
	public function testAny() {
		$this->req_content->server['request_method'] = 'get';
		$this->req_content->server['request_uri'] = '/user/someone';
		$this->router->get('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'update'
		], [
			'id' => '(\d+)'
		]);
		$this->router->any('user/:id', [
			'module' => 'index',
			'controller' => 'user',
			'action' => 'view'
		]);
		$this->router->parse($this->req);
		$this->assertEquals('view', $this->req->action);
	}
}