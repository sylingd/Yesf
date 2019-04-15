<?php
/**
 * HTTP请求封装
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2019 ShuangYa
 * @license https://yesf.sylibs.com/license.html
 */
namespace Yesf\Http;

use Yesf\Yesf;
use Yesf\DI\Container;

class Request {
	private $cookie_handler;
	private $sw_request;
	private $extra_infos = [];
	private $session = null;
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
	/**
	 * Get original post body
	 * 
	 * @access public
	 * @return string
	 */
	public function rawContent() {
		return $this->sw_request->rawContent();
	}
	/**
	 * Get uploaded files
	 * 
	 * @access public
	 * @return array
	 */
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
	/**
	 * Get session
	 * 
	 * @access public
	 * @return object
	 */
	public function session() {
		if ($this->session === null) {
			$name = Yesf::app()->getConfig('session.name');
			$type = Yesf::app()->getConfig('session.type');
			if ($name === null) {
				$name = 'YESFSESSID';
			}
			if ($type === null) {
				$type = 'cookie';
			}
			$handler = Container::getInstance()->get(Dispatcher::class)->getSessionHandler();
			if ($type === 'cookie') {
				if (!isset($this->cookie[$name])) {
					do {
						$id = Session::generateId();
					} while ($handler->read($id) !== '');
					$saved = '';
					$this->cookie_handler[0]->{$this->cookie_handler[1]}($name, $id, 0, '/');
				} else {
					$id = $this->cookie[$name];
					$saved = $handler->read($id);
				}
			} else {
				if (!isset($this->get[$name])) {
					do {
						$id = Session::generateId();
					} while ($handler->read($id) !== '');
					$saved = '';
				} else {
					$id = $this->get[$name];
					$saved = $handler->read($id);
				}
			}
			$this->session = new Session($id, $saved);
		}
		return $this->session;
	}
	/**
	 * Set cookie handler, used by session
	 * 
	 * @access public
	 * @param callable $handler Cookie handler
	 */
	public function setCookieHandler($handler) {
		$this->cookie_handler = $handler;
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
	/**
	 * Finish request, release resources
	 * 
	 * @access public
	 */
	public function end() {
		$this->get = null;
		$this->post = null;
		$this->server = null;
		$this->header = null;
		$this->cookie = null;
		$this->files = null;
		$this->sw_request = null;
		if ($this->session !== null) {
			$handler = Container::getInstance()->get(Dispatcher::class)->getSessionHandler();
			$handler->write($this->session->id(), $this->session->encode());
			$this->session = null;
		}
	}
	public function __destruct() {
		$this->end();
	}
}
