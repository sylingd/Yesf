<?php
/**
 * 数据库基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\database;
use \SplQueue;
use \Swoole\Coroutine as co;
use yesf\library\Swoole;

abstract class DatabaseAbstract {
	protected $config = NULL;
	protected $connection = NULL;
	protected $connection_count = 0;
	protected $last_run_out_time = NULL;
	protected $wait = NULL;
	public function __construct(array $config) {
		$this->wait = new SplQueue;
		$this->connection = new SplQueue;
		$this->config = $config;
		$this->last_run_out_time = time();
		//建立最小连接
		$count = Database::getMinClientCount(get_class($this));
		while ($count--) {
			$this->createConnection();
		}
	}
	/**
	 * 获取一个可用连接
	 * 如果不存在可用连接，会自动判断是否需要建立新的连接
	 * @access protected
	 * @return object
	 */
	protected function getConnection() {
		$uid = co::getuid();
		if ($this->connection->count() === 0) {
			//是否需要建立新的连接
			if (Database::getMaxClientCount(get_class($this)) > $this->connection_count) {
				$this->last_run_out_time = time();
				$this->connect();
				return $this->connection->pop();
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
	 * @access protected
	 * @param object $connection
	 */
	protected function freeConnection($connection) {
		$this->connection->push($connection);
		if (count($this->wait) > 0) {
			$id = $this->wait->pop();
			co::resume($id);
		} else {
			//有连接处于空闲状态超过15秒，关闭一个连接
			if ($this->connection_count > Database::getMinClientCount(get_class($this)) && time() - $this->last_run_out_time > 15) {
				$this->close();
			}
		}
	}
	/**
	 * 断开一个连接
	 * @access protected
	 */
	protected function close() {
		$this->connection_count--;
	}
	/**
	 * 创建新的连接，并压入连接池
	 * @access protected
	 */
	protected function createConnection() {
		$this->connection->push($this->connect());
		$this->connection_count++;
	}
	/**
	 * 创建新的连接并返回
	 * @access protected
	 */
	abstract protected function connect();
	/**
	 * 执行查询并返回结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	abstract public function query(string $sql, $data = NULL);
	/**
	 * 执行查询并返回一条结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get(string $sql, $data = NULL) {
		$r = $this->query($sql, $data);
		return count($r) > 0 ? current($r) : NULL;
	}
	/**
	 * 执行查询并返回一条结果中的一列
	 * 可以只传入前两个参数，而不传入$column，此时$data将会当做$column处理
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @param string $column 列名
	 * @return array
	 */
	public function getColumn(string $sql, $data = NULL, $column = NULL) {
		if ($column === NULL) {
			if ($data === NULL) {
				throw new DBException('$column can not be empty');
			} else {
				$column = $data;
			}
		}
		$result = $this->get($sql, $data);
		if ($result === NULL || !isset($result[$column])) {
			throw new DBException("Column $column not exists");
		} else {
			return $result[$column];
		}
	}
}