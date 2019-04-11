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

namespace Yesf\Http;
use Yesf\Yesf;

class Request {
	private $sw_request;
	private $extra_infos = [];
	public $extension = null;
	public $module = null;
	public $controller = null;
	public $action = null;
	public $param = [];
	public $request_uri = '';
	/** from swoole */
	public $get;
	public $post;
	public $server;
	public $header;
	public $cookie;
	public $files;
	public function __construct($req) {
		$this->sw_request = $req;
		$this->get = &$req->get;
		$this->post = &$req->post;
		$this->server = &$req->server;
		$this->header = &$req->header;
		$this->cookie = &$req->cookie;
		$this->files = &$req->files;
	}
	public function rawContent() {
		return $this->sw_request->rawContent();
	}
	public function file() {
		static $res = null;
		if ($res === null) {
			$res = [];
			foreach ($this->files as $v) {
				$res[] = new File($v);
			}
		}
		return $res;
	}
	public function __get($name) {
		if (isset($this->extra_infos[$name])) {
			return $this->extra_infos[$name];
		}
		return null;
	}
	public function __isset($name) {
		return isset($this->extra_infos[$name]);
	}
	public function __set($name, $value) {
		$this->extra_infos[$name] = $value;
	}
	public function __unset($name) {
		unset($this->extra_infos[$name]);
	}
	public function __destruct() {
		$this->sw_request = null;
	}
}
