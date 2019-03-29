<?php
namespace YesfTest\RD\Adapter;

use PDO;
use Swoole\Coroutine as co;
use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Connection\Pool;

class MysqlTest extends TestCase {
	private $adapter;
	private $pdo;
	public function setUp() {
		$dsn = sprintf(
			'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
			Yesf::app()->getConfig('connection.my.host'),
			Yesf::app()->getConfig('connection.my.port'),
			Yesf::app()->getConfig('connection.my.database')
		);
		$this->pdo = new PDO($dsn, Yesf::app()->getConfig('connection.my.user'), Yesf::app()->getConfig('connection.my.password'));
		$that = $this;
		go(function() use ($that) {
			$that->adapter = Pool::getAdapter('my');
		});
	}
	/*
	public function testGet() {
		$that = $this;
		go(function() use ($that) {
			$that->adapter->get('SELECT * FROM `user`');
		});
	}
	*/
	public function testGetColumn() {
		$that = $this;
		go(function() use ($that) {
			// Wait while connection
			while ($that->adapter === null) {
				co::sleep(1);
			}
			$r1 = $that->adapter->get('SELECT count(*) as n FROM `user`', 'n');
			$r2 = $that->pdo->query('SELECT count(*) as n FROM `user`')->fetch(PDO::FETCH_ASSOC);
			$that->assertSame($r1, $r2['n']);
		});
	}
	/*
	public function testSelect() {
	}
	public function testInsert() {
	}
	*/
}