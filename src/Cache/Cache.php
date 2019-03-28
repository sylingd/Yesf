<?php
/**
 * 缓存常用操作类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Relational Database
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 */

namespace Yesf\Cache;

use Yesf\Yesf;
use Yesf\Exception\Exception;
use Psr\SimpleCache\CacheInterface;

class Cache {
	private static $db = [];
	private static $adapter = [];
	/**
	 * 通过读取配置，获取数据库操作类
	 * 
	 * @access public
	 * @param string $type 类型
	 * @return object
	 */
	public static function get($config = null) {
	}
}