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

class RestfulRouter implements RouterInterface {
	/**
	 * 基本路径
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	protected $prefix = '/';
	protected $routes;
	public function __construct() {
		$this->routes = [];
	}
	public function add($type, $rule, $action, $options = null) {
		$param = [];
		$regex = str_replace('/', '\\/', $rule);
		$regex = preg_replace_callback('/:([a-zA-Z0-9_]+)/', function($matches) use (&$param, &$options) {
			$paramInfo = [
				'name' => $matches[1]
			];
			if (is_array($options) && isset($options[$paramInfo['name']])) {
				$paramInfo['validate'] = '/^' . $options[$paramInfo['name']] . '$/';
			}
			$param[] = $paramInfo;
			return '([^\/]+)';
		}, $regex);
		$regex = '/^' . $regex . '$/';
		if (!isset($this->routes[$type])) {
			$this->routes[$type] = [];
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
	private function parseBy($rules, $uri) {
		foreach ($rules as $rewrite) {
			if (preg_match($rewrite['regex'], $uri, $matches)) {
				$param = [];
				unset($matches[0]);
				//参数
				foreach ($rewrite['param'] as $k => $v) {
					$it = $matches[$k + 1];
					if (isset($v['validate']) && !preg_match($v['validate'], $it)) {
						continue 2;
					}
					$param[$v['name']] = $it;
				}
				$dispatch = $rewrite['dispatch'];
				return [
					'dispatch' => $dispatch,
					'param' => $param
				];
			}
		}
		return null;
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
		$res = null;
		if (isset($this->routes[$request->server['request_method']])) {
			$res = $this->parseBy($this->routes[$request->server['request_method']], $uri);
		}
		if ($res === null && isset($this->routes['any'])) {
			$res = $this->parseBy($this->routes['any'], $uri);
		}
		if ($res === null) {
			return;
		}
		$request->param = $res['param'];
		$request->module = isset($res['dispatch']['module']) ? $res['dispatch']['module'] : $this->module;
		$request->controller = $res['dispatch']['controller'];
		$request->action = $res['dispatch']['action'];
	}
}