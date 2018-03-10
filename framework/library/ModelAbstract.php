<?php
/**
 * Model基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;

use \yesf\library\exception\Exception;
use \yesf\library\database\Database;

abstract class ModelAbstract {
	protected $table_name = '';
	protected $primary_key = 'id';
	private static $_instance = NULL;
	/**
	 * 单例化
	 * @access public
	 * @return object(ModelAbstract)
	 */
	public static function getInstance() {
		if (self::$_instance === NULL) {
			self::$_instance = new static;
		}
		return self::$_instance;
	}
	public function __construct() {
		//检查table_name是否为空
		if (empty($this->table_name)) {
			throw new Exception('Table name can not be empty');
		}
	}
	/**
	 * 获取Builder实例类
	 * @access public
	 * @return object
	 */
	public function builder() {
		return Database::getBuilder();
	}
	public function select() {
		return $this->builder()->newSelect()->from($this->table_name);
	}
	public function insert() {
		return $this->builder()->newInsert()->from($this->table_name);
	}
	public function update() {
		return $this->builder()->newUpdate()->from($this->table_name);
	}
	public function delete() {
		return $this->builder()->newDelete()->from($this->table_name);
	}
	/**
	 * 执行一条SQL语句
	 * @access public
	 * @param string $sql
	 * @param array $data
	 * @return array
	 */
	public function exec($sql, $data = []) {
		return Database::get()->query($sql, $data);
	}
	/**
	 * 执行一条Builder的结果
	 * @access public
	 * @param object $builder
	 * @return array
	 */
	public function execBuilder($builder) {
		return $this->exec($query->getStatement(), $query->getBindValues());
	}
	/**
	 * 查询一条数据
	 * @access public
	 * @param mixed $filter 当$filter为array时，则为多条条件，否则为主键
	 */
	public function get($filter) {
		if (!is_array($filter)) {
			$key = $this->primary_key;
			$value = $filter;
		} else {
			$key = key($filter);
			$value = current($filter);
		}
		$sql = $this->select()->where($key . ' = ?', [$value])->limit(1);
		$result = $this->execBuilder($sql);
		return $result === FALSE ? FALSE : current($result);
	}
}
