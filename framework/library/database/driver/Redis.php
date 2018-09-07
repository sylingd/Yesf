<?php
/**
 * Redis封装类
 * 在某些环境下，可能需要用户使用co::create手动创建协程环境
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\database\driver;

use \yesf\Yesf;
use \yesf\library\exception\Exception;
use \yesf\library\exception\DBException;
use \yesf\library\database\DatabaseAbstract;
use \yesf\library\database\DatabaseInterface;
use \Swoole\Coroutine as co;

class Redis extends DatabaseAbstract {
	private $options = [];
	/**
	 * 断开当前连接
	 * 
	 * @access public
	 */
	protected function close() {
		$connection = $this->getConnection();
		$connection->close();
		parent::close();
	}
	/**
	 * 根据配置连接到数据库
	 * 
	 * @access protected
	 */
	protected function connect() {
		$connection = new co\Redis();
		$r = $connection->connect($this->config['host'], $this->config['port']);
		if ($r === FALSE) {
			throw new DBException('Can not connect to database server, ' . $connection->errMsg, $connection->errCode);
		}
		if (!empty($this->config['password'])) {
			$r = $connection->auth($this->config['password']);
			if ($r === FALSE) {
				throw new DBException('Authenticate failed, ' . $connection->errMsg, $connection->errCode);
			}
		}
		if (!empty($this->config['name'])) {
			$r = $connection->select($this->config['name']);
			if ($r === FALSE) {
				throw new DBException('Select database failed, ' . $connection->errMsg, $connection->errCode);
			}
		}
		foreach ($this->options as $k => $v) {
			$connection->setOption($k, $v);
		}
		return $connection;
	}
	/**
	 * 魔术方法，调用随机连接
	 * 
	 * @access public
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		$connection = $this->getConnection();
		if (!method_exists($connection, $name)) {
			$this->freeConnection($connection);
			throw new Exception('Method ' . $name . ' not exists');
		}
		$result = $connection->$name(...$arguments);
		$this->freeConnection($connection);
		return $result;
	}
	/**
	 * setOption封装
	 * 会对所有连接执行，并压入一个数组，每次新建连接会自动执行
	 * 此方法的执行会阻塞所有连接，直到所有连接都执行完成
	 * 
	 * @access public
	 * @param int $name
	 * @param mixed $value
	 */
	public function setOption($name, $value) {
		$all = [];
		for ($i = 1; $i <= $this->connection_count; $i++) {
			$connection = $this->getConnection();
			$connection->setOption($name, $value);
			$all[] = $connection;
		}
		foreach ($all as $v) {
			$this->freeConnection($v);
		}
	}
	/**
	 * 获取单个连接
	 * 注意：此方法需要手动释放连接
	 * 
	 * @access public
	 * @return object(Redis)
	 */
	public function lockConnection() {
		return $this->getConnection();
	}
	/**
	 * 返回一个连接到连接池
	 * 
	 * @access public
	 * @param object(Redis) $conn
	 */
	public function unlockConnection($conn) {
		$this->freeConnection($conn);
	}
}