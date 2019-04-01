<?php
namespace YesfTest;

use PHPUnit\Framework\TestCase;
use Yesf\DI\Container;
use YesfApp\Model\User;

class ModelTest extends TestCase {
	public static $pdo;
	public static $model;
	public static function setUpBeforeClass() {
		$dsn = sprintf(
			'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
			Yesf::app()->getConfig('connection.my.host'),
			Yesf::app()->getConfig('connection.my.port'),
			Yesf::app()->getConfig('connection.my.database')
		);
		self::$pdo = new PDO($dsn, Yesf::app()->getConfig('connection.my.user'), Yesf::app()->getConfig('connection.my.password'));
		self::$model = Container::getInstance()->get(User::class);
	}
	public function testGet() {
		$user = self::$pdo->query('SELECT * FROM `user` LIMIT 0,1')->fetch(PDO::FETCH_ASSOC);
		$res = self::$model->get($user['id']);
		$this->assertSame($user['name'], $res['name']);
	}
	public function testList() {
	}
	public function testSet() {
		$user = self::$pdo->query('SELECT * FROM `user` LIMIT 0,1')->fetch(PDO::FETCH_ASSOC);
		$newName = uniqid();
		self::$model->set(['name' => $newName], $user['id']);
		$res = self::$pdo->query("SELECT name FROM `user` WHERE id = {$user['id']} LIMIT 0,1")->fetch(PDO::FETCH_ASSOC);
		$this->assertSame($newName, $res['name']);
	}
	public function testAdd() {
		$name = uniqid();
		$password = uniqid();
		$password_hashed = password_hash($password, PASSWORD_DEFAULT);
		$res = self::$model->add([
			'name' => $name,
			'password' => $password_hashed
		]);
		$this->assertEquals(1, $res['_affected_rows']);
		$this->assertTrue(isset($res['_insert_id']));
		$selected = self::$pdo->query('SELECT * FROM `user` WHERE id = ' . $res['_insert_id'])->fetch(PDO::FETCH_ASSOC);
		$this->assertSame($name, $selected['name']);
		$this->assertTrue(password_verify($password, $selected['password']));
	}
	public function testDel() {
		$record = self::$pdo->query('SELECT * FROM `user` LIMIT 0,1')->fetch(PDO::FETCH_ASSOC);
		$res = self::$model->del($record['id']);
		$this->assertEquals(1, $res['_affected_rows']);
		$selected = self::$pdo->query('SELECT count(*) as n FROM `user` WHERE id = ' . $record['id'])->fetchColumn();
		$this->assertEquals(0, intval($selected));
	}
}