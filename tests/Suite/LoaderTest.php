<?php
use \PHPUnit\Framework\TestCase;
use \org\bovigo\vfs\vfsStream;
use \yesf\library\Loader;

class LoaderTest extends PHPUnit_Framework_TestCase {
	private $root;
	public function setUp() {
		$this->root = vfsStream::setup();
		// Generate some files
		$this->mkdirs($this->root->url() . '/ClassMap');
		$this->mkdirs($this->root->url() . '/Psr0/Foo/Bar');
		$this->mkdirs($this->root->url() . '/Psr0-2/Foo/Bar');
		$this->mkdirs($this->root->url() . '/Psr4/Foo/Bar');
		$this->mkdirs($this->root->url() . '/Psr4-2/Foo/Bar');
		file_put_contents($this->root->url() . '/ClassMap/Test1.php', '');
		file_put_contents($this->root->url() . '/ClassMap/Test2.php', '');
		file_put_contents($this->root->url() . '/Psr0/Foo/Bar/Demo.php', '');
		file_put_contents($this->root->url() . '/Psr0/Foo/Bar/Demo2.php', '');
		file_put_contents($this->root->url() . '/Psr0-2/Foo/Bar/Demo.php', '');
		file_put_contents($this->root->url() . '/Psr0-2/Foo/Bar/Demo2.php', '');
		file_put_contents($this->root->url() . '/Psr4/Foo/Bar/Demo.php', '');
		file_put_contents($this->root->url() . '/Psr4-2/Foo/Bar/Demo.php', '');
	}
	private function mkdirs($dir) {
		$parent = substr($dir, 0, strrpos($dir, '/'));
		if (!is_dir($parent)) {
			$this->mkdirs($parent);
		}
		mkdir($dir);
	}
	private function getFileDir($file) {
		return $this->root->url() . '/' . $file;
	}
	public function testPsr0() {
		Loader::addPsr0('Foo\\Bar', [$this->getFileDir('Psr0')]);
		$this->assertEquals(Loader::findFile('Foo\\Bar\\Demo'), $this->getFileDir('Psr0/Foo/Bar/Demo.php'));
		$this->assertEquals(Loader::findFile('Foo\\Bar_Demo2'), $this->getFileDir('Psr0/Foo/Bar/Demo2.php'));
		Loader::clearPsr0();
		// Test prepend when prefix is null
		Loader::addPsr0(null, [$this->getFileDir('Psr0-2')], true);
		Loader::addPsr0(null, [$this->getFileDir('Psr0')], false);
		$this->assertEquals(Loader::findFile('Foo\\Bar\\Demo'), $this->getFileDir('Psr0-2/Foo/Bar/Demo.php'));
		Loader::clearPsr0();
		Loader::addPsr0(null, [$this->getFileDir('Psr0-2')], false);
		Loader::addPsr0(null, [$this->getFileDir('Psr0')], true);
		$this->assertEquals(Loader::findFile('Foo\\Bar\\Demo'), $this->getFileDir('Psr0/Foo/Bar/Demo.php'));
		Loader::clearPsr0();
		// Test prepend when prefix is not null
		Loader::addPsr0('Foo', [$this->getFileDir('Psr0-2')], false);
		Loader::addPsr0('Foo', [$this->getFileDir('Psr0')], true);
		$this->assertEquals(Loader::findFile('Foo\\Bar\\Demo'), $this->getFileDir('Psr0/Foo/Bar/Demo.php'));
		Loader::clearPsr0();
		Loader::addPsr0('Foo', [$this->getFileDir('Psr0-2')], true);
		Loader::addPsr0('Foo', [$this->getFileDir('Psr0')], false);
		$this->assertEquals(Loader::findFile('Foo\\Bar\\Demo'), $this->getFileDir('Psr0-2/Foo/Bar/Demo.php'));
		Loader::clearPsr0();
	}
	public function testPsr4() {
		Loader::addPsr4('Psr4\\', [$this->getFileDir('Psr4')]);
		$this->assertEquals(Loader::findFile('Psr4\\Foo\\Bar\\Demo'), $this->getFileDir('Psr4/Foo/Bar/Demo.php'));
		Loader::clearPsr4();
		$this->assertFalse(Loader::findFile('Psr4\\Foo\\Bar\\Demo'));
		$this->assertFalse(Loader::findFile('\\Psr4\\Foo\\Bar\\Demo'));
		Loader::clearPsr4();
		// When add a invalid prefix
		$hasException = FALSE;
		try {
			Loader::addPsr4('INVALID', [$this->getFileDir('Psr4')]);
		} catch (\Exception $e) {
			$hasException = TRUE;
			$this->assertEquals($e->getMessage(), 'A non-empty PSR-4 prefix must end with a namespace separator.');
		}
		$this->assertTrue($hasException);
		Loader::clearPsr4();
	}
	public function testClassMap() {
		Loader::addClassMap([
			'Test1' => $this->getFileDir('ClassMap/Test1.php'),
			'Test2' => $this->getFileDir('ClassMap/Test2.php')
		]);
		$this->assertEquals(Loader::findFile('Test1'), $this->getFileDir('ClassMap/Test1.php'));
		Loader::clearClassMap();
		$this->assertFalse(Loader::findFile('Test2'));
	}
}