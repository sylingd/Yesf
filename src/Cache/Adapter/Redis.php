<?php
/**
 * Redis
 * 在某些环境下，可能需要用户使用co::create手动创建协程环境
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Cache
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;

use Yesf\Connection\Pool;

class Redis implements CacheInterface {
	private $pool;
	public function __construct() {
		$this->pool = Pool::get('redis'); // Redis
	}
	public function get($key, $default = null) {
		$result = $this->pool->get($key);
		return $result === false ? $default : $result;
	}
	public function set($key, $value, $ttl = null) {
		return $this->pool->set($key, $value, $ttl);
	}
	public function delete($key) {
		return $this->pool->delete($key);
	}
	public function clear() {
		// TODO
	}
	public function getMultiple($keys, $default = null) {
		$result = $this->pool->mGet($keys);
		if ($default !== null) {
			foreach ($result as $k => $v) {
				if ($v === false) {
					$result[$k] = $default[$k];
				}
			}
		}
		return $result;
	}
	public function setMultiple($values, $ttl = null) {
		$redis->mSet($values);
		// TODO: ttl
	}
	public function deleteMultiple($keys) {
		return $this->pool->delete($keys);
	}
	public function has($key) {
		return $this->pool->exists($key);
	}
}