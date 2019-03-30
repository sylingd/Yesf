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
use Yesf\Yesf;
use Yesf\Exception\Exception;
use Yesf\Exception\RequirementException;

class Yac implements CacheInterface {
	private $handler;
	public function __construct() {
		if (!class_exists(\Yac::class)) {
			throw new RequirementException("Extension Yac is required");
		}
		$prefix = Yesf::app()->getConfig('cache.yac.prefix');
		if (!$prefix) {
			$prefix = '';
		}
		$len = YAC_MAX_KEY_LEN - 32;
		if (strlen($prefix) > $len) {
			throw new Exception("Prefix length must be less than $len");
		}
		$this->handler = new \Yac($prefix);
	}
	private function getKey($key) {
		return strlen($key) > 32 ? md5($key) : $key;
	}
	public function get($key, $default = null) {
		$res = $this->handler->get($this->getKey($key));
		if ($res === false) {
			return $default;
		}
		return $res;
	}
	public function set($key, $value, $ttl = 0) {
		$this->handler->set($this->getKey($key), $value, $ttl);
	}
	public function delete($key) {
		$this->handler->delete($this->getKey($key));
	}
	public function clear() {
		$this->handler->flush();
	}
	public function getMultiple($keys, $default = null) {
		foreach ($keys as $k => $v) {
			$keys[$k] = $this->getKey($v);
		}
		$result = $this->handler->get($keys);
		foreach ($result as $k => $v) {
			if ($v === false) {
				if (is_array($default)) {
					if (isset($default[$k])) {
						$result[$k] = $default[$k];
					} else {
						$result[$k] = null;
					}
				} else {
					$result[$k] = $default;
				}
			} else {
				$result[$k] = $v;
			}
		}
		return $result;
	}
	public function setMultiple($values, $ttl = 0) {
		$toSet = [];
		foreach ($values as $k => $v) {
			$toSet[$this->getKey($k)] = $v;
		}
		$this->handler->set($toSet, $ttl);
	}
	public function deleteMultiple($keys) {
		foreach ($keys as $k => $v) {
			$keys[$k] = $this->getKey($v);
		}
		$this->handler->delete($keys);
	}
	public function has($key) {
		return $this->get($key) !== null;
	}
}