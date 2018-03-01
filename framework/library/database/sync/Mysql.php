<?php
/**
 * MySQL简单封装类
 * 此类为同步方式，通过PDO实现
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\database\sync;

use \PDO;
use \PDOException;
use \yesf\Yesf;
use \yesf\library\database\DababaseInterface;
use \yesf\library\exception\DBException;

class Mysql implements DababaseInterface {
	protected $config = NULL;
	protected $connection = NULL;
	public function __construct($config) {
		$this->set($config);
	}
	/**
	 * 断开当前连接
	 * 由于PDO没有直接提供close，因此简单的通过置空，使其自动释放
	 * @access public
	 */
	public function close() {
		$this->connection = NULL;
	}
	/**
	 * 设置连接信息
	 * 设置新的信息会导致当前连接断开
	 * @access public
	 * @param array $config
	 */
	public function set($config) {
		$this->close();
		$this->config = $config;
		$this->connect();
	}
	/**
	 * 根据配置连接到数据库
	 * @access protected
	 */
	protected function connect() {
		try {
			$this->connection = new PDO(
				sprintf(
					'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
					$this->config['host'],
					$this->config['port'],
					$this->config['database']
				),
				$this->config['user'],
				$this->config['password']
			);
		} catch (PDOException $e) {
			throw new DBException($e->getMessage(), $e->getCode());
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
	public function query($sql, $data = NULL, $tryAgain = TRUE) {
		if (is_array($data) && count($data) >0) {
			try {
				$st = $this->connection->prepare($sql);
			} catch (PDOException $e) {
				goto SQL_TRY_AGAIN;
			}
			if ($st === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			$r = $st->execute($data);
			if ($r === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			return $st->fetchAll(PDO::FETCH_ASSOC);
		} else {
			$r = $this->connection->query($sql);
			if ($r === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			return $r->fetchAll(PDO::FETCH_ASSOC);
		}
SQL_TRY_AGAIN:
		if ($this->connection->errorCode() === 2006 && $tryAgain) {
			$this->connect();
			return $this->query($sql, $data, FALSE);
		} else {
			throw new DBException($this->connection->errorInfo(), $this->connection->errorCode());
		}
	}
	/**
	 * 执行查询并返回一条结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get($sql, $data = NULL) {
		if (!preg_match('/limit ([0-9,]+)$/i', $sql)) {
			$sql .= ' LIMIT 0,1';
		}
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
	/**
	 * 获取最后一个插入的ID
	 * @access public
	 * @return string
	 */
	public function getLastId() {
		return $this->link->lastInsertId();
	}
}
