<?php
/**
 * 路由解析类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\http\Response;

class Router {
	protected static $rewrite = [];
	protected static $regex = [];
	protected static $modules = NULL;
	/**
	 * 按照Map方式解析路由
	 * 对于请求request_uri为"/ap/foo/bar"
	 * base_uri为"/ap"
	 * 则最后参加路由的request_uri为"/foo/bar"
	 */
	public static function parseMap($uri) {
		//解析
		$uri = explode('/', $uri, 3);
		if (count($uri) === 3) {
			//解析到的结果包括了module
			$dispatch = [
				'module' => $uri[0],
				'controller' => $uri[1],
				'action' => $uri[2]
			];
		} else {
			$dispatch = [
				'controller' => $uri[0],
				'action' => $uri[1]
			];
		}
		return [[], $dispatch];
	}
	/**
	 * 按照Rewrite方式解析路由
	 */
	public static function parseRewrite($uri) {
		foreach (self::$rewrite as $rewrite) {
			if (preg_match($rewrite['regexp'], $uri, $matches)) {
				$param = [];
				unset($matches[0]);
				//参数
				foreach ($rewrite['param'] as $k => $v) {
					$param[$v] = $matches[$k + 1];
				}
				//处理星号部分
				if (count($rewrite['param']) != count($matches)) {
					end($matches);
					$ext_param = explode('/', current($matches));
					foreach ($ext_param as $k => $v) {
						if ($k % 2 === 0) {
							$param[$v] = $ext_param[$k + 1];
						}
					}
				}
				$dispatch = $rewrite['dispatch'];
				break;
			}
		}
		if (isset($param)) {
			return [$param, $dispatch];
		} else {
			return NULL;
		}
	}
	/**
	 * 添加Rewrite解析规则
	 * @param string $rule 基本规则
	 * @param array $dispatch 分发规则
	 */
	public static function addRewrite($rule, $dispatch) {
		//将规则解析为正则
		$param = [];
		$regexp = str_replace('/', '\\/', $rule);
		$regexp = preg_replace_callback('/:([a-zA-Z0-9_]+)/', function($matches) use (&$param) {
			$param[] = $matches[1];
			return '([^\/]+)';
		}, $regexp);
		//处理结尾出的星号
		if (substr($regexp, -1, 1) === '*') {
			$regexp = preg_replace('/\*$/', '(.*?)', $regexp);
		}
		$regexp = '/^' . $regexp . '$/';
		self::$rewrite[] = [
			'regexp' => $regexp,
			'rule' => $rule,
			'param' => $param,
			'dispatch' => $dispatch
		];
	}
	/**
	 * 按照Regex方式解析路由
	 */
	public static function parseRegex($uri) {
		foreach (self::$regex as $regex) {
			if (preg_match($regex['regexp'], $uri, $matches)) {
				$param = [];
				unset($matches[0]);
				foreach ($matches as $k => $v) {
					$param[$regex['param'][$k]] = $v;
				}
				$dispatch = $regex['dispatch'];
				break;
			}
		}
		if (isset($param)) {
			return [$param, $dispatch];
		} else {
			return NULL;
		}
	}
	/**
	 * 添加Regex解析规则
	 * @param string $rule 规则
	 * @param array $dispatch 分发规则
	 * @param array $param 参数列表
	 */
	public static function addRegex($rule, $dispatch, $param) {
		self::$regex[] = [
			'regexp' => $rule,
			'param' => $param,
			'dispatch' => $dispatch
		];
	}
	/**
	 * 判断路由是否合法
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return int
	 */
	public static function isValid($module, $controller, $action) {
		$controllerName = Yesf::app()->getConfig('application.namespace') . '\\controller\\' . $module . '\\' . ucfirst($controller);
		if (!class_exists($controllerName, FALSE)) {
			if (self::$modules === NULL) {
				self::$modules = explode(',', Yesf::app()->getConfig('application.modules'));
			}
			if (!in_array($module, self::$modules, TRUE)) {
				return Constant::ROUTER_ERR_MODULE;
			}
			//判断controller是否存在并加载
			$controllerPath = APP_PATH . 'modules/' . $module . '/controllers/' . $controller. '.php';
			if (!is_file($controllerPath)) {
				return Constant::ROUTER_ERR_CONTROLLER;
			}
			require($controllerPath);
		}
		if (!method_exists($controllerName, $action . 'Action')) {
			return Constant::ROUTER_ERR_ACTION;
		}
		return Constant::ROUTER_VALID;
	}
	public static function route($routeInfo, $request, $response) {
		$result = NULL;
		$module = isset($routeInfo['module']) ? $routeInfo['module'] : Yesf::app()->getConfig('application.module');
		$controller = empty($routeInfo['controller']) ? 'index' : $routeInfo['controller'];
		$action = empty($routeInfo['action']) ? 'index' : $routeInfo['action'];
		$viewDir = Yesf::app()->getConfig('application.dir') . 'modules/' . $module . '/views/';
		$yesfResponse = new Response($response, $controller . '/' . $action, $viewDir);
		if (!empty($request->extension)) {
			$yesfResponse->mimeType($request->extension);
		}
		if (($code = self::isValid($module, $controller, $action)) === Constant::ROUTER_VALID) {
			$controllerName = Yesf::app()->getConfig('application.namespace') . '\\controller\\' . $module . '\\' . ucfirst($controller);
			if (version_compare(PHP_VERSION, '7.0.0', '<') && version_compare(SWOOLE_VERSION, '2.0.0', '>=')) {
				$result = \Swoole\Coroutine::call_user_func([$controllerName, $action . 'Action'], $request, $yesfResponse);
			} else {
				$result = call_user_func([$controllerName, $action . 'Action'], $request, $yesfResponse);
			}
		} else {
			$yesfResponse->disableView();
			$yesfResponse->status(404);
		}
		unset($request, $response, $yesfResponse);
		return $result;
	}
}