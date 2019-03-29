<?php
/**
 * 设置基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Config;

trait ConfigTrait {
	private $replace = [];
	/**
	 * 魔术方法，方便调用
	 */
	public function __get($k) {
		return $this->get($k);
	}
	public function __isset($k) {
		return $this->has($k);
	}
	public function has($key) {
		return $this->get($key) !== null;
	}
}