<?php
/**
 * 连接池接口
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Connection;

interface PoolInterface {
	/**
	 * Setup connection pool
	 * 
	 * @access public
	 * @param array $config
	 */
	public function initPool($config);
	/**
	 * Get a connection from pool
	 * 
	 * @access public
	 * @return object
	 */
	public function getConnection();
	/**
	 * Put a connection into pool
	 * 
	 * @access public
	 * @param object $connection
	 */
	public function freeConnection($connection);
	/**
	 * Close a connection
	 * 
	 * @access public
	 */
	public function close();
	/**
	 * Re-connect
	 * 
	 * @access public
	 * @param object $connection
	 */
	public function reconnect($connection);
}