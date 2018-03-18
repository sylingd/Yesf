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
use \yesf\library\exception\DBException;

abstract class ModelAbstract {
	protected static $_table_name = '';
	protected static $_primary_key = 'id';
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
		if (empty(static::$_table_name)) {
			throw new Exception('Table name can not be empty');
		}
	}
	/**
	 * 获取Builder实例类
	 * @access public
	 * @return object
	 */
	public static function builder() {
		return Database::getBuilder();
	}
	public static function select() {
		return static::builder()->newSelect()->from(static::$_table_name);
	}
	public static function insert() {
		return static::builder()->newInsert()->into(static::$_table_name);
	}
	public static function update() {
		return static::builder()->newUpdate()->table(static::$_table_name);
	}
	public static function delete() {
		return static::builder()->newDelete()->from(static::$_table_name);
	}
	/**
	 * 执行一条SQL语句
	 * @access public
	 * @param string $sql
	 * @param array $data
	 * @return array
	 */
	public static function execute($sql, $data = []) {
		return Database::get()->query($sql, $data);
	}
	/**
	 * 执行一条Builder的结果
	 * @access public
	 * @param object $builder
	 * @return array
	 */
	public static function executeBuilder($builder) {
		list($st, $vals) = $builder->getStatementAndValues(TRUE);
		return static::execute($st, $vals);
	}
	/**
	 * 查询一条数据
	 * @access public
	 * @param mixed $filter 当$filter为array时，则为多条条件，否则为主键
	 * @param array $cols 需要查询出的列
	 */
	public static function get($filter, $cols = NULL) {
		if (!is_array($filter)) {
			$key = static::$_primary_key;
			$value = $filter;
		} else {
			$key = key($filter);
			$value = current($filter);
		}
		$query = static::select();
		if (is_array($cols)) {
			$query->cols($cols);
		}
		$query->where($key . ' = :' . $key, [$key => $value])->limit(1);
		$result = static::executeBuilder($query);
		return count($result) > 0 ? current($result) : NULL;
	}
	/**
	 * 查询多条数据
	 * @access public
	 * @param array $filter
	 * @param int $num
	 * @param int $offset
	 * @param array $cols 需要查询出的列
	 * @return array
	 */
	public static function list($filter = [], $num = 30, $offset = 0, $cols = NULL) {
		$query = static::select();
		if (is_array($cols)) {
			$query->cols($cols);
		}
		foreach ($filter as $k => $v) {
			$query->where($k . ' = :' . $k, [$k => $v]);
		}
		$query->limit($num)->offset($offset);
		return static::executeBuilder($query);
	}
	/**
	 * 修改一条或多条数据
	 * 注意：$filter不能为空，如果要更新所有数据，请设置$filter为TRUE
	 * 
	 * @access public
	 * @param array $set
	 * @param array $filter
	 */
	public static function set($set, $filter) {
		$query = static::update();
		$query->cols($set);
		if ($filter !== TRUE) {
			if (is_string($filter) || is_numeric($filter)) {
				$query->where(static::$_primary_key . ' = :' . static::$_primary_key, [
					static::$_primary_key => $filter
				]);
			} elseif (!is_array($filter) || count($filter) === 0) {
				throw new DBException("Filter can not be empty");
			} else {
				foreach ($filter as $k => $v) {
					$query->where($k . ' = :' . $k, [$k => $v]);
				}
			}
		}
		return static::executeBuilder($query);
	}
	/**
	 * 删除数据
	 * 注意：$filter不能为空，如果要清除所有数据，请设置$filter为TRUE
	 * 
	 * @access public
	 * @param array|string|int|boolean $filter
	 */
	public static function del($filter) {
		$query = static::delete();
		if ($filter !== TRUE) {
			if (is_string($filter) || is_numeric($filter)) {
				$query->where(static::$_primary_key . ' = :' . static::$_primary_key, [
					static::$_primary_key => $filter
				]);
			} elseif (!is_array($filter) || count($filter) === 0) {
				throw new DBException("Filter can not be empty");
			} else {
				foreach ($filter as $k => $v) {
					$query->where($k . ' = :' . $k, [$k => $v]);
				}
			}
		}
		return static::executeBuilder($query);
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
	public static function add(array $data) {
		$query = static::insert()->cols($data);
		static::executeBuilder($query);
		if (!empty(static::$_primary_key)) {
			return intval(Database::get()->getLastId());
		} else {
			return NULL;
		}
	}
}
