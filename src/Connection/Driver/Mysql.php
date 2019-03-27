<?php
/**
 * MySQL封装类
 * 在某些环境下，可能需要用户使用co::create手动创建协程环境
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Driver
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Connection\Driver;

use Yesf\Yesf;
use Yesf\Exception\ConnectionException;
use Swoole\Coroutine as co;

class Mysql {
	use PoolTrait;
	protected $config = NULL;
	public function getMinClient() {
		return Database::getMinClientCount(get_class($this));
	}
	public function getMaxClient() {
		return Database::getMaxClientCount(get_class($this));
	}
	public function __construct(array $config) {
		$this->config = $config;
		$this->initPool();
	}
	/**
	 * 根据配置连接到数据库
	 * 
	 * @access protected
	 */
	protected function connect() {
		$connection = new co\MySQL();
		$r = $connection->connect([
			'host' => $this->config['host'],
			'user' => $this->config['user'],
			'password' => $this->config['password'],
			'database' => $this->config['database'],
			'port' => $this->config['port'],
			'timeout' => 3,
			'charset' => 'utf8'
		]);
		if ($r === FALSE) {
			throw new ConnectionException('Can not connect to database server');
		}
		return $connection;
	}
}
