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
	private $extra_infos = [];
	public $extension = NULL;
	public $param = [];
	public $request_uri = '';
	public function __construct($sw_request) {
		$this->sw_request = $sw_request;
	}
	public function rawContent() {
		return $this->sw_request->rawContent();
	}
	public function __get($name) {
		if (isset($this->extra_infos[$name])) {
			return $this->extra_infos[$name];
		}
		if (isset($this->sw_request->{$name})) {
			return $this->sw_request->{$name};
		 }
		 return NULL;
	}
	public function __isset($name) {
		return isset($this->extra_infos[$name]) || isset($this->sw_request->{$name});
	}
	public function __set($name, $value) {
		$this->extra_infos[$name] = $value;
	}
	public function __unset($name) {
		unset($this->extra_infos[$name]);
	}
	public function __destruct() {
		$this->sw_request = NULL;
	}
}
