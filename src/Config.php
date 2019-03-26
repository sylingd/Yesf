<?php
/**
 * 设置基本类
 * 现已支持Yaconf、QConf
 * Yaconf文档：http://www.laruence.com/2015/06/12/3051.html
 * QConf文档：https://github.com/Qihoo360/QConf/wiki/QConf-PHP-Doc
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf;
use \Yaconf;
use \yesf\Yesf;
use \yesf\Constant;

class Config {
	protected $appName;
	protected $environment;
	protected $type;
	protected $conf;
	//替换掉已有的配置，最高优先级
	protected $replaceConf = [];
	public function __construct($conf, $appName = NULL) {
		$this->appName = $appName;
		$this->environment = Yesf::app()->getEnvironment();
		if (is_array($conf)) {
			$this->type = Constant::CONFIG_FILE;
			$this->conf = $conf;
		} elseif ($conf === Constant::CONFIG_YACONF) {
			$this->type = Constant::CONFIG_YACONF;
		} elseif ($conf === Constant::CONFIG_QCONF) {
			$this->type = Constant::CONFIG_QCONF;
		} elseif (is_file($conf)) {
			if (extension_loaded('Yaf')) {
				$this->type = Constant::CONFIG_YAF;
				if (class_exists('\\Yaf_Config_Ini', FALSE)) {
					$this->conf = new \Yaf_Config_Ini($conf, $this->environment);
				} else {
					$this->conf = new \Yaf\Config\Ini($conf, $this->environment);
				}
			} else {
				$this->type = Constant::CONFIG_FILE;
				$this->conf = $this->parseIniConfig($conf);
			}
		} else {
			//throw new SYException();
		}
	}
	/**
	 * 当不存在Yaf时，进行配置的解析
	 * 支持配置继承，但不支持多级继承
	 */
	protected function parseIniConfig($conf) {
		$conf = parse_ini_file($conf, TRUE);
		$mresult = NULL;
		$result = [];
		//有继承的情况
		if (!isset($conf[$this->environment])) {
			$environments = array_keys($conf);
			foreach ($environments as $one) {
				if (strpos($one, ':') === FALSE) {
					continue;
				}
				list($child, $parent) = explode(':', $one);
				$child = trim($child);
				if ($child === $this->environment) {
					//找到合适的配置了
					$parent = trim($parent);
					$mresult = array_merge($conf[$parent], $conf[$one]);
				}
			}
			if ($mresult === NULL) {
				return [];
			}
		} else {
			$mresult = $conf[$this->environment];
		}
		//将“.”作为分隔符，分割为多维数组
		foreach ($mresult as $k => $v) {
			if (strpos($k, '.') === FALSE) {
				$result[$k] = $v;
				continue;
			}
			$keys = explode('.', $k);
			$total = count($keys) - 1;
			$parent = &$result;
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
		return $result;
	}
	/**
	 * 获取配置
	 * @access public
	 * @param string $key 形似aaa.bbb.ccc的key，会根据不同类型自动处理
	 */
	public function get($key) {
		if (isset($this->replaceConf[$key])) {
			return $this->replaceConf[$key];
		}
		switch ($this->type) {
			case Constant::CONFIG_YACONF:
				return $this->getByYaconf($key);
			case Constant::CONFIG_QCONF:
				return $this->getByQconf($key);
			case Constant::CONFIG_YAF:
				return $this->getByYaf($key);
			case Constant::CONFIG_FILE:
				return $this->getByConf($key);
		}
		return NULL;
	}
	public function getByYaconf($key) {
		$key = $this->environment . '.' . $key;
		if (!empty($this->appName)) {
			$key = $this->appName . '.' . $key;
		}
		return Yaconf::has($key) ? Yaconf::get($key) : NULL;
	}
	public function getByQconf($key) {
		$key = '/' . $this->environment . '.' . $key;
		$key = str_replace('.', '/', $key);
		if (!empty($this->appName)) {
			$key = '/' . $this->appName . $key;
		}
		return getConf($key);
	}
	public function getByYaf($key) {
		$rs = $this->conf->get($key);
		if (is_object($rs)) {
			$rs = $rs->toArray();
		}
		return $rs;
	}
	public function getByConf($key) {
		$key = explode('.', $key);
		$conf = $this->conf;
		foreach ($key as $v) {
			if (isset($conf[$v])) {
				$conf = $conf[$v];
			} else {
				return NULL;
			}
		}
		return $conf;
	}
	/**
	 * 替换掉默认配置
	 * @access public
	 * @param string $key
	 * @param mixed $val
	 */
	public function replace($key, $val) {
		$this->replaceConf[$key] = $val;
	}
	public function has($key) {
		return $this->get($key) !== NULL;
	}
	/**
	 * 魔术方法，方便调用
	 */
	public function __get($k) {
		return $this->get($k);
	}
	public function __set($k , $v) {
		$this->replace($k, $v);
	}
	public function __isset($k) {
		return $this->has($k);
	}
}