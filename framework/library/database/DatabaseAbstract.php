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

abstract class DatabaseAbstract {
	protected $config = NULL;
	protected $connection = NULL;
	public function __construct(array $config) {
		$this->set($config);
	}
	/**
	 * 断开当前连接
	 * 由于PDO没有直接提供close，因此简单的通过置空，使其自动释放
	 * @access public
	 */
	abstract public function close();
	/**
	 * 设置连接信息
	 * 设置新的信息会导致当前连接断开
	 * @access public
	 * @param array $config
	 */
	public function set(array $config) {
		$this->close();
		$this->config = $config;
		$this->connect();
	}
	/**
	 * 根据配置连接到数据库
	 * @access protected
	 */
	abstract protected function connect();
	/**
	 * 执行查询并返回结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @param boolean $tryAgain 发生“MySQL has gone away”错误时是否重试
	 * @return array
	 */
	abstract public function query(string $sql, $data = NULL, $tryAgain = TRUE);
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
	/**
	 * 获取最后一个插入的ID
	 * @access public
	 * @return string
	 */
	abstract public function getLastId();
}