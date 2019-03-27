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

class Redis implements CacheInterface {
	public function get($key, $default = null) {
		//
	}
	public function set($key, $value, $ttl = null) {
		//
	}
	public function delete($key) {
		//
	}
	public function clear() {
		//
	}
	public function getMultiple($keys, $default = null) {
		//
	}
	public function setMultiple($values, $ttl = null) {
		//
	}
	public function deleteMultiple($keys) {
		//
	}
	public function has($key) {
		//
	}
}