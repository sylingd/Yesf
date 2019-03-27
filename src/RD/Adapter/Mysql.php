<?php
/**
 * MySQL封装类
 * 在某些环境下，可能需要用户使用co::create手动创建协程环境
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Relational Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Database\Adapter;

use Yesf\Yesf;
use Yesf\Exception\DBException;
use Yesf\Connection\Mysql as MysqlConnection;
use Yesf\Database\Database;
use Yesf\Database\DatabaseInterface;
use Swoole\Coroutine as co;

class Mysql extends MysqlConnection implements DatabaseInterface {
	/**
	 * 执行查询并返回结果
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function query(string $sql, $data = null) {
		$connection = $this->getConnection();
		$result = null;
		$tryAgain = true;
SQL_START_EXECUTE:
		if (is_array($data) && count($data) > 0) {
			try {
				$st = $connection->prepare($sql);
				if ($st === false) {
					goto SQL_TRY_AGAIN;
				}
				if (is_object($st)) {
					$result = $st->execute($data);
				} else {
					$result = $connection->execute($data);
				}
			} catch (\Throwable $e) {
				goto SQL_TRY_AGAIN;
			}
			if ($result === false) {
				goto SQL_TRY_AGAIN;
			}
			goto SQL_SUCCESS_RETURN;
		} else {
			try {
				$result = $connection->query($sql);
			} catch (\Throwable $e) {
				goto SQL_TRY_AGAIN;
			}
			if ($result === false) {
				goto SQL_TRY_AGAIN;
			}
			goto SQL_SUCCESS_RETURN;
		}
SQL_TRY_AGAIN:
		if (($connection->errno === 2006 || $connection->errno === 2013) && $tryAgain) {
			$tryAgain = false;
			$connection->connect([
				'host' => $this->config['host'],
				'user' => $this->config['user'],
				'password' => $this->config['password'],
				'database' => $this->config['database'],
				'port' => $this->config['port'],
				'timeout' => 3,
				'charset' => 'utf8'
			]);
			goto SQL_START_EXECUTE;
		} else {
			$error = $connection->error;
			$errno = $connection->errno;
			$this->freeConnection($connection);
			throw new DBException($error, $errno);
		}
SQL_SUCCESS_RETURN:
		if ($result === true) {
			$result = [
				'_affected_rows' => $connection->affected_rows
			];
			if (stripos($sql, 'insert') === 0) {
				$result['_insert_id'] = $connection->insert_id;
			}
		}
		$this->freeConnection($connection);
		return $result;
	}
	/**
	 * 执行查询并返回一条结果
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get(string $sql, $data = null) {
		if (!preg_match('/limit ([0-9,]+)$/i', $sql)) {
			$sql .= ' LIMIT 0,1';
		}
		$r = $this->query($sql, $data);
		return count($r) > 0 ? current($r) : null;
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
	public function getColumn(string $sql, $data = null, $column = null) {
		if ($column === null) {
			if ($data === null) {
				throw new DBException('$column can not be empty');
			} else {
				$column = $data;
			}
		}
		$result = $this->get($sql, $data);
		if ($result === null || !isset($result[$column])) {
			throw new DBException("Column $column not exists");
		} else {
			return $result[$column];
		}
	}
}
