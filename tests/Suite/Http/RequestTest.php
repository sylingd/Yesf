<?php
use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Http\Request;
use YesfApp\Http\FakeRequest;

class RequestTest extends TestCase {
	private $fake_req;
	public function setUp() {
		$this->fake_req = new FakeRequest;
	}
	public function testRequest() {
		$req = clone $this->fake_req;
		$req->raw_content = uniqid();
		$request = new Request($req);
		$this->assertSame($req->raw_content, $request->rawContent());
		$id = uniqid();
		$this->assertFalse(isset($request->test));
		$request->test = $id;
		$this->assertTrue(isset($request->test));
		$this->assertSame($id, $request->test);
		unset($request->test);
		$this->assertFalse(isset($request->test));
		$this->assertEquals('test', $request->get['action']);
		$this->assertNull($request->hahaha);
	}
}