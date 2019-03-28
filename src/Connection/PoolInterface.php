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
	public function initPool();
	public function getConnection();
	public function freeConnection($connection);
	public function close();
	public function reconnect($connection);
}