<?php
/**
 * 路由解析类
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

class Router implements RouterInterface {
	/**
	 * 基本路径
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	private $prefix = '/';
	private $routes;
	private $enable_map;
	private $enable_extension;
	public function __construct() {
		$this->routes = [];
		$config = Yesf::app()->getConfig('router', Yesf::CONF_PROJECT);
		$this->enable_map = is_array($config) && isset($config['map']) ? $config['map'] : true;
		$this->enable_extension = is_array($config) && isset($config['extension']) ? $config['extension'] : false;
	}
	public function add($type, $rule, $action, $options = null) {
		$param = [];
		$regex = str_replace('/', '\\/', $rule);
		$regex = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function($matches) use (&$param, &$options) {
			$paramName = $matches[1];
			$param[] = $paramName;
			if (is_array($options) && isset($options[$paramName])) {
				return $options[$paramName];
			} else {
				return '([^\/]+)';
			}
		}, $regex);
		$regex = '/^' . $regex . '$/';
		if (!isset($this->routes[$type])) {
			$this->routes[$type] = [];
		}
		if (is_string($action)) {
			$res = explode('.', $action, 3);
			if (count($res) === 3) {
				$action = [
					'module' => $res[0],
					'controller' => $res[1],
					'action' => $res[2]
				];
			} else {
				$action = [
					'controller' => $res[0],
					'action' => $res[1]
				];
			}
		}
		$this->routes[$type][] = [
			'regex' => $regex,
			'param' => $param,
			'dispatch' => $action
		];
	}
	public function any($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function get($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function post($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function put($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function delete($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function head($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function options($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function connect($rule, $action, $options = null) {
		$this->add(__FUNCTION__, $rule, $action, $options);
	}
	public function setPrefix($rule = '/') {
		$this->rule = $rule;
	}
	private function parseMap(Request $request) {
		//解析
		$res = explode('/', $request->uri, 3);
		if (count($res) === 3) {
			$request->module = $res[0];
			$request->controller = $res[1];
			$request->action = $res[2];
		} else {
			$request->module = $this->module;
			$request->controller = $res[0];
			$request->action = $res[1];
		}
		return true;
	}
	private function parseBy($rules, Request $request) {
		foreach ($rules as $rewrite) {
			if (preg_match($rewrite['regex'], $request->uri, $matches)) {
				$param = [];
				unset($matches[0]);
				//参数
				foreach ($rewrite['param'] as $k => $v) {
					$param[$v] = $matches[$k + 1];
				}
				$dispatch = $rewrite['dispatch'];
				if ($dispatch instanceof \Closure) {
					$dispatch = $dispatch($param);
					if ($dispatch === null) {
						continue;
					}
				}
				$request->param = $param;
				$request->module = isset($dispatch['module']) ? $dispatch['module'] : $this->module;
				$request->controller = $dispatch['controller'];
				$request->action = $dispatch['action'];
				return true;
			}
		}
		return false;
	}
	public function enableMap() {
		$this->enable_map = true;
	}
	public function disableMap() {
		$this->enable_map = false;
	}
	public function parse(Request $request) {
		$len = strlen($this->prefix);
		//路由解析
		$uri = $request->server['request_uri'];
		if (strpos('?', $uri) !== false) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		//去除开头的prefix
		if (strpos($uri, $this->prefix) === 0) {
			$uri = substr($uri, $len);
		}
		$request->uri = $uri;
		$res = false;
		if (isset($this->routes[$request->server['request_method']])) {
			$res = $this->parseBy($this->routes[$request->server['request_method']], $request);
		}
		if ($res === false && isset($this->routes['any'])) {
			$res = $this->parseBy($this->routes['any'], $request);
		}
		if ($res === false) {
			if ($this->enable_map) {
				//为空则读取默认设置
				if (empty($uri)) {
					$request->module = $this->module;
					$request->controller = 'index';
					$request->action = 'index';
				} else {
					if ($this->enable_extension) {
						$hasPoint = strrpos($uri, '.');
						if ($hasPoint !== false) {
							$request->extension = substr($uri, $hasPoint + 1);
							$uri = substr($uri, 0, $hasPoint);
							$request->uri = $uri;
						}
					}
					$this->parseMap($request);
				}
			}
		}
	}
}