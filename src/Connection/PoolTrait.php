<?php
/**
 * 连接池基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Connection;

use SplQueue;
use Yesf\Swoole;
use Yesf\Exception\NotFoundException;
use Swoole\Coroutine as co;

trait PoolTrait {
	protected $connection = NULL;
	protected $connection_count = 0;
	protected $last_run_out_time = NULL;
	protected $wait = NULL;
	public function initPool() {
		if (!method_exists($this, 'getMinClient') || !method_exists($this, 'getMaxClient')) {
			throw new NotFoundException("Method getMinClient or getMaxClient not found");
		}
		$this->wait = new SplQueue;
		$this->connection = new SplQueue;
		$this->last_run_out_time = time();
		//建立最小连接
		$count = $this->getMinClient();
		while ($count--) {
			$this->createConnection();
		}
	}
	/**
	 * 获取一个可用连接
	 * 如果不存在可用连接，会自动判断是否需要建立新的连接
	 * 
	 * @access public
	 * @return object
	 */
	public function getConnection() {
		if ($this->connection->count() === 0) {
			//是否需要建立新的连接
			if ($this->getMaxClient() > $this->connection_count) {
				$this->last_run_out_time = time();
				return $this->connect();
			}
			//wait
			$this->wait->push($uid);
			co::suspend();
			return $this->connection->pop();
		}
		if ($this->connection->count() === 1) {
			$this->last_run_out_time = time();
		}
		return $this->connection->pop();
	}
	/**
	 * 使用完成连接，归还给连接池
	 * 
	 * @access public
	 * @param object $connection
	 */
	public function freeConnection($connection) {
		$this->connection->push($connection);
		if (count($this->wait) > 0) {
			$id = $this->wait->pop();
			co::resume($id);
		} else {
			//有连接处于空闲状态超过15秒，关闭一个连接
			if ($this->connection_count > $this->getMinClient() && time() - $this->last_run_out_time > 15) {
				$this->close();
			}
		}
	}
	/**
	 * 断开一个连接
	 * 
	 * @access protected
	 */
	protected function close() {
		$this->connection_count--;
	}
	/**
	 * 创建新的连接，并压入连接池
	 * 
	 * @access protected
	 */
	protected function createConnection() {
		$this->connection->push($this->connect());
		$this->connection_count++;
	}
	/**
	 * 创建新的连接并返回
	 * 
	 * @access protected
	 */
	protected function connect() {
	}
}