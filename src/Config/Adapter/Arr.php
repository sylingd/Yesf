<?php
/**
 * Array设置支持类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Config\Adapter;

use SplQueue;
use Yesf\Yesf;
use Yesf\Exception\Exception;
use Yesf\Config\ConfigTrait;
use Yesf\Config\ConfigInterface;

class Arr implements ConfigInterface {
	use ConfigTrait;
	protected $environment;
	protected $conf;
	public function __construct(array $conf) {
		$this->environment = Yesf::app()->getEnvironment();
		$this->conf = $conf;
	}
	/**
	 * 获取配置
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @param mixed $default 默认
	 * @return mixed
	 */
	public function get($key, $default = null) {
		$keys = explode('.', $key);
		$conf = $this->conf[$this->environment];
		foreach ($keys as $v) {
			if (isset($conf[$v])) {
				$conf = $conf[$v];
			} else {
				return $default;
			}
		}
		return $conf;
	}
	/**
	 * 检查配置是否存在
	 * @access public
	 * @param string $key 形似a.b.c的key
	 * @return bool
	 */
	public function has($key) {
		$keys = explode('.', $key);
		$conf = $this->conf[$this->environment];
		foreach ($keys as $v) {
			if (isset($conf[$v])) {
				$conf = $conf[$v];
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	 * 当不存在Yaf时，进行配置的解析
	 * 支持配置继承，但不支持多级继承
	 */
	public static function fromIniFile($file) {
		if (!is_file($file)) {
			throw new Exception("Config file $file not found");
		}
		$conf = parse_ini_file($file, true);
		$all = [];
		$queue = new SplQueue;
		$environments = array_keys($conf);
		foreach ($environments as $one) {
			if (strpos($one, ':') === false) {
				$queue->push($one);
			}
		}
		while ($queue->count() > 0) {
			$it = $queue->pop();
			if (strpos($it, ':') === false) {
				$all[$it] = $conf[$it];
				$child = $it; 
			} else {
				list($child, $parent) = explode(':', $it, 2);
				$child = trim($child);
				$parent = trim($parent);
				$all[$child] = array_merge($all[$parent], $conf[$it]);
			}
			// Add childrens into queue
			foreach ($environments as $one) {
				if (!isset($all[$one]) && strpos($one, ':') !== false) {
					$one_parse = explode(':', $one, 2);
					if ($one_parse[1] === $child) {
						$queue->push($one);
					}
				}
			}
		}
		$result = [];
		//将“.”作为分隔符，分割为多维数组
		foreach ($all as $env => $it) {
			$result[$env] = [];
			foreach ($it as $k => $v) {
				if (strpos($k, '.') === false) {
					$result[$env][$k] = $v;
					continue;
				}
				$keys = explode('.', $k);
				$total = count($keys) - 1;
				$parent = &$result[$env];
				foreach ($keys as $kk => $vv) {
					if ($total === $kk) {
						$parent[$vv] = $v;
					} else {
						if (!isset($parent[$vv])) {
							$parent[$vv] = [];
						}
						$parent = &$parent[$vv];
					}
				}
			}
		}
		return new self($result);
	}
}