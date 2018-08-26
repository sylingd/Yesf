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

namespace yesf\library\database\driver;

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
		$this->getConnection();
		parent::close();
	}
	/**
	 * 根据配置连接到数据库
	 * @access protected
	 */
	protected function connect() {
		$connection = new co\MySQL();
		$r = $connection->connect([
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
		return $connection;
	}
	/**
	 * 执行查询并返回结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function query(string $sql, $data = NULL) {
		$connection = $this->getConnection();
		$result = NULL;
		$tryAgain = TRUE;
SQL_START_EXECUTE:
		if (is_array($data) && count($data) > 0) {
			$st = $connection->prepare($sql);
			if ($st === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			try {
				if (is_object($st)) {
					$result = $st->execute($data);
				} else {
					$result = $connection->execute($data);
				}
			} catch (\Throwable $e) {
				goto SQL_TRY_AGAIN;
			}
			if ($result === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			goto SQL_SUCCESS_RETURN;
		} else {
			try {
				$result = $connection->query($sql);
			} catch (\Throwable $e) {
				goto SQL_TRY_AGAIN;
			}
			if ($result === FALSE) {
				goto SQL_TRY_AGAIN;
			}
			goto SQL_SUCCESS_RETURN;
		}
SQL_TRY_AGAIN:
		if ($connection->errno === 2006 && $tryAgain) {
			$tryAgain = FALSE;
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
			throw new DBException($connection->error, $connection->errno);
		}
SQL_SUCCESS_RETURN:
		if ($result === TRUE) {
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
}
