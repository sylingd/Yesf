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

namespace Yesf;

use \Yesf\Database\Database;
use \Yesf\Exception\Exception;
use \Yesf\Exception\DBException;

abstract class ModelAbstract {
	protected static $_table_name = '';
	protected static $_primary_key = 'id';
	private static $_instance = [];
	/**
	 * 单例化
	 * @access public
	 * @return object(ModelAbstract)
	 */
	public static function getInstance() {
		$name = get_called_class();
		if (!isset(self::$_instance[$name])) {
			$clazz = new static;
			self::$_instance[$name] = $clazz;
			return $clazz;
		}
		return self::$_instance[$name];
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
		$query = static::select();
		if (is_array($cols)) {
			$query->cols($cols);
		}
		if (!is_array($filter)) {
			$key = static::$_primary_key;
			$query->where($key . ' = :' . $key, [$key => $filter]);
		} else {
			foreach ($filter as $k => $v) {
				if (is_int($k)) {
					if (is_array($v)) {
						$query->where($v[0], $v[1]);
					} else {
						$query->where($v);
					}
				} else {
					$query->where($k . ' = :' . $k, [$k => $v]);
				}
			}
		}
		$query->limit(1);
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
			if (is_int($k)) {
				if (is_array($v)) {
					$query->where($v[0], $v[1]);
				} else {
					$query->where($v);
				}
			} else {
				$query->where($k . ' = :' . $k, [$k => $v]);
			}
		}
		$query->limit($num)->offset($offset);
		return static::executeBuilder($query);
	}
	/**
	 * 修改一条或多条数据
	 * 当传入两个参数时，会认为第二个参数是$filter
	 * 注意：$filter不能为空，如果要更新所有数据，必须传入$filter为TRUE
	 * 
	 * @access public
	 * @param array $set
	 * @param array $cols
	 * @param array $filter
	 * @return int
	 */
	public static function set($set, $cols, $filter = NULL) {
		if ($filter === NULL) {
			$filter = &$cols;
		} else {
			//筛选$set列
			foreach ($set as $k => $v) {
				if (!in_array($k, $cols, TRUE)) {
					unset($set[$k]);
				}
			}
		}
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
					if (is_int($k)) {
						if (is_array($v)) {
							$query->where($v[0], $v[1]);
						} else {
							$query->where($v);
						}
					} else {
						$query->where($k . ' = :' . $k, [$k => $v]);
					}
				}
			}
		}
		$result = static::executeBuilder($query);
		return intval($result['_affected_rows']);
	}
	/**
	 * 删除数据
	 * 注意：$filter不能为空，如果要清除所有数据，必须传入$filter为TRUE
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
					if (is_int($k)) {
						if (is_array($v)) {
							$query->where($v[0], $v[1]);
						} else {
							$query->where($v);
						}
					} else {
						$query->where($k . ' = :' . $k, [$k => $v]);
					}
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
	 * @param array $cols
	 * @return int/null
	 */
	public static function add(array $data, $cols = NULL) {
		if (is_array($cols)) {
			//筛选$data列
			foreach ($data as $k => $v) {
				if (!in_array($k, $cols, TRUE)) {
					unset($data[$k]);
				}
			}
		}
		$query = static::insert()->cols($data);
		$result = static::executeBuilder($query);
		if (!empty(static::$_primary_key)) {
			return intval($result['_insert_id']);
		} else {
			return NULL;
		}
	}
}
