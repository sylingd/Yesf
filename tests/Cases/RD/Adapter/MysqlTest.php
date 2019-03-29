<?php
namespace YesfTest\RD\Adapter;

use PDO;
use PHPUnit\Framework\TestCase;
use Yesf\Yesf;
use Yesf\Connection\Pool;

class MysqlTest extends TestCase {
	public static $pdo;
	public static function setUpBeforeClass() {
		$dsn = sprintf(
			'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
			Yesf::app()->getConfig('connection.my.host'),
			Yesf::app()->getConfig('connection.my.port'),
			Yesf::app()->getConfig('connection.my.database')
		);
		self::$pdo = new PDO($dsn, Yesf::app()->getConfig('connection.my.user'), Yesf::app()->getConfig('connection.my.password'));
	}
	public static function getAdapter() {
		return Pool::getAdapter('my');
	}
	public function testGet() {
		$r1 = self::getAdapter()->get('SELECT * FROM `user` LIMIT 0,1');
		$r2 = self::$pdo->query('SELECT * FROM `user` LIMIT 0,1')->fetch(PDO::FETCH_ASSOC);
		$this->assertSame($r1, $r2);
	}
	public function testGetColumn() {
		$r1 = self::getAdapter()->getColumn('SELECT count(*) as n FROM `user`', 'n');
		$r2 = self::$pdo->query('SELECT count(*) as n FROM `user`')->fetch(PDO::FETCH_ASSOC);
		$this->assertSame($r1, $r2['n']);
	}
	/*
	public function testSelect() {
		$r1 = self::getAdapter()->get('SELECT * FROM `user` ORDER BY id ASC');
		$r2 = self::$pdo->query('SELECT * FROM `user` ORDER BY id ASC')->fetch(PDO::FETCH_ASSOC);
		$this->assertSame($r1, $r2);
	}
	public function testInsert() {
	}
	*/
}