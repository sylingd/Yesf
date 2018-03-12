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
use yesf\library\exception\DBException;

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
		return $this->builder()->newInsert()->into($this->table_name);
	}
	public function update() {
		return $this->builder()->newUpdate()->table($this->table_name);
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
		list($st, $vals) = $builder->getStatementAndValues(TRUE);
		return $this->exec($st, $vals);
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
		$query = $this->select()->where($key . ' = :' . $key, [$key => $value])->limit(1);
		$result = $this->execBuilder($query);
		return $result === FALSE ? FALSE : current($result);
	}
	/**
	 * 查询多条数据
	 * @access public
	 * @param array $filter
	 * @param int $num
	 * @param int $offset
	 * @return array
	 */
	public function list($filter = [], $num = 30, $offset = 0) {
		$query = $this->select();
		foreach ($filter as $k => $v) {
			$query->where($k . ' = :' . $k, [$k => $v]);
		}
		$query->limit($num)->offset($offset);
		return $this->execBuilder($query);
	}
	/**
	 * 修改一条或多条数据
	 * @access public
	 * @param array $set
	 * @param array $filter
	 */
	public function set($set, $filter) {
		$query = $this->update();
		$query->cols($set);
		foreach ($filter as $k => $v) {
			$query->where($k . ' = :' . $k, [$k => $v]);
		}
		return $this->execBuilder($query);
	}
	/**
	 * 删除数据
	 * 注意：$filter不能为空，如果要清除所有数据，请设置$filter为TRUE
	 * 
	 * @access public
	 * @param array $filter
	 */
	public function del($filter) {
		$query = $this->delete();
		if ($filter !== TRUE) {
			if (!is_array($filter) || count($filter) === 0) {
				throw new DBException("Filter can not be empty");
			}
			foreach ($filter as $k => $v) {
				$query->where($k . ' = :' . $k, [$k => $v]);
			}
		}
		return $this->execBuilder($query);
	}
	/**
	 * 添加数据
	 * 如果指定了$primary_key，则会返回最后一次生成的ID
	 * 否则返回NULL
	 * 
	 * @access public
	 * @param array $data
	 * @return int/null
	 */
	public function add(array $data) {
		$query = $this->insert()->cols($data);
		$this->execBuilder($query);
		if (!empty($this->primary_key)) {
			return intval(Database::get()->getLastId());
		} else {
			return NULL;
		}
	}
}
