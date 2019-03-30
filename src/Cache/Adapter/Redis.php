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
use Yesf\Connection\PoolInterface;

class Redis implements CacheInterface {
	private $pool;
	public function __construct(PoolInterface $pool) {
		$this->pool = $pool;
	}
	public function get($key, $default = null) {
		$result = $this->pool->get($key);
		return $result === false ? $default : unserialize($result);
	}
	public function set($key, $value, $ttl = null) {
		return $this->pool->set($key, serialize($value), $ttl);
	}
	public function delete($key) {
		return $this->pool->delete($key);
	}
	public function clear() {
		// TODO
	}
	public function getMultiple($keys, $default = null) {
		$result = $this->pool->mGet($keys);
		foreach ($result as $k => $v) {
			if ($v === false) {
				if (is_array($default) && isset($default[$k])) {
					$result[$k] = $default[$k];
				} else {
					$result[$k] = $default;
				}
			} else {
				$result[$k] = unserialize($v);
			}
		}
		return $result;
	}
	public function setMultiple($values, $ttl = null) {
		foreach ($values as $k => $v) {
			$values[$k] = serialize($v);
		}
		$this->pool->mSet($values);
		// TODO: ttl
	}
	public function deleteMultiple($keys) {
		return $this->pool->delete($keys);
	}
	public function has($key) {
		return $this->pool->exists($key);
	}
}