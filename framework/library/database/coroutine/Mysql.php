<?php
/**
 * MySQL简单封装类
 * 此类为协程方式，在某些环境下可能无法使用（例如Task进程中）
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\database\coroutine;

use \yesf\Yesf;
use \yesf\library\exception\DBException;
use \yesf\library\database\DatabaseAbstract;
use \yesf\library\database\DatabaseInterface;
use \Swoole\Coroutine as co;

class Mysql extends DatabaseAbstract implements DatabaseInterface {
	/**
	 * 断开当前连接
	 * 由于Swoole没有直接提供close，因此简单的通过置空，使其自动释放
	 * @access public
	 */
	public function close() {
		$this->connection = NULL;
	}
	/**
	 * 根据配置连接到数据库
	 * @access protected
	 */
	protected function connect() {
		$this->connection = new co\MySQL();
		$r = $this->connection->connect([
			'host' => $this->config['host'],
			'user' => $this->config['user'],
			'password' => $this->config['password'],
			'database' => $this->config['database'],
			'port' => $this->config['port'],
			'timeout' => 3,
			'charset' => 'utf8'
		]);
		if ($r === FALSE) {
			throw new DBException('Can not connect to database server');
		}
	}
	/**
	 * 执行查询并返回结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @param boolean $tryAgain 发生“MySQL has gone away”错误时是否重试
	 * @return array
	 */
	public function query(string $sql, $data = NULL, $tryAgain = TRUE) {
		if (is_array($data) && count($data) >0) {
			$st = $this->connection->prepare($sql);
			if ($st === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			$r = $st->execute($data);
			if ($r === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			return $r;
		} else {
			$r = $this->connection->query($sql);
			if ($r === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			return $r;
		}
SQL_TRY_AGAIN:
		if ($this->connection->errno === 2006 && $tryAgain) {
			$this->connect();
			return $this->query($sql, $data, FALSE);
		} else {
			throw new DBException($this->connection->error, $this->connection->errno);
		}
	}
	/**
	 * 执行查询并返回一条结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get(string $sql, $data = NULL) {
		if (!preg_match('/limit ([0-9,]+)$/i', $sql)) {
			$sql .= ' LIMIT 0,1';
		}
		return parent::get($sql, $data);
	}
	/**
	 * 获取最后一个插入的ID
	 * @access public
	 * @return string
	 */
	public function getLastId() {
		return $this->connection->insert_id;
	}
}
