<?php
/**
 * HTTP请求封装
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\http;
use \yesf\Yesf;

class Request {
	private $sw_request;
	public $extension = NULL;
	public $param = [];
	public function __construct($sw_request) {
		$this->sw_request = $sw_request;
	}
	public function rawContent() {
		return $this->sw_request->rawContent();
	}
	public function __get($name) {
		return isset($this->sw_request->{$name}) ? $this->sw_request->{$name} : NULL;
	}
	public function __isset($name) {
		return isset($this->sw_request->{$name});
	}
	public function __destruct() {
		$this->sw_request = NULL;
	}
}
