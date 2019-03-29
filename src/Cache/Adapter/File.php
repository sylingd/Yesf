<?php
/**
 * 文件
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

class File implements CacheInterface {
	public function __construct() {
	}
	public function get($key, $default = null) {
	}
	public function set($key, $value, $ttl = null) {
	}
	public function delete($key) {
	}
	public function clear() {
		// TODO
	}
	public function getMultiple($keys, $default = null) {
	}
	public function setMultiple($values, $ttl = null) {
	}
	public function deleteMultiple($keys) {
	}
	public function has($key) {
	}
}