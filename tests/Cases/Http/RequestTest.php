<?php
namespace YesfTest\Http;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Http\Request;
use YesfApp\Http\FakeRequest;

class RequestTest extends TestCase {
	private static $fake_req;
	public static function setUpBeforeClass() {
		self::$fake_req = new FakeRequest;
	}
	public function testRequest() {
		$req = clone self::$fake_req;
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