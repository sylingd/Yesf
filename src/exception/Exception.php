<?php
/**
 * 普通异常类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Exception
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\exception;

use \RuntimeException;

class Exception extends RuntimeException {
	protected $info = [];
	public function __construct($message, $code = 0) {
		if (is_array($message)) {
			$this->info = $message;
			if (isset($message['message'])) {
				$this->message = $message['message'];
			}
			if (isset($message['code'])) {
				$this->code = $message['code'];
			}
		} else {
			parent::__construct($message, $code);
		}
	}
	public function __get($name) {
		return isset($this->info[$name]) ? $this->info[$name] : NULL;
	}
}