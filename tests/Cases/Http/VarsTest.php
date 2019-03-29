<?php
namespace YesfTest\Http;

use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Http\Vars;

class VarsTest extends TestCase {
	public function testMimeType() {
		$mime = require(YESF_ROOT . 'Data/mimeTypes.php');
		$this->assertSame($mime['flv'], Vars::mimeType('Flv'));
		$this->assertEquals('text/html; charset=' . Yesf::getProjectConfig('charset'), Vars::mimeType('html'));
		$this->assertEquals('application/octet-stream', Vars::mimeType('unknown'));
	}
}