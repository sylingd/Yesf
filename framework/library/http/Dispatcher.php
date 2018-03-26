<?php
/**
 * 请求分发类
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
use \yesf\Constant;
use \yesf\library\http\Response;

class Dispatcher {
	private static $modules = NULL;
	private static $default_module = 'index';
	private static $default_action = 'index';
	private static $default_controller = 'index';
	/**
	 * 设置默认模块
	 * @param string $module
	 */
	public static function setDefaultModule($module) {
		self::$default_module = $module;
	}
	/**
	 * 设置默认控制器
	 * @param string $controller
	 */
	public static function setDefaultController($controller) {
		self::$default_controller = $controller;
	}
	/**
	 * 设置默认功能
	 * @param string $action
	 */
	public static function setDefaultAction($action) {
		self::$default_action = $action;
	}
	/**
	 * 判断路由是否合法
	 * @codeCoverageIgnore
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return int
	 */
	public static function isValid($module, $controller, $action) {
		$controllerName = Yesf::getAppNamespace() . '\\controller\\' . $module . '\\' . ucfirst($controller);
		if (!class_exists($controllerName, FALSE)) {
			if (self::$modules === NULL) {
				self::$modules = explode(',', Yesf::app()->getConfig('application.modules'));
			}
			if (!in_array($module, self::$modules, TRUE)) {
				return Constant::ROUTER_ERR_MODULE;
			}
			//判断controller是否存在
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
	/**
	 * 进行路由分发
	 * @codeCoverageIgnore
	 * @access public
	 * @param array $routeInfo 路由信息
	 * @param object $request 来自Swoole的请求内容
	 * @param object $response 来自Swoole的响应对象
	 * @return mixed
	 */
	public static function dispatch($routeInfo, $request, $response) {
		$result = NULL;
		$module = empty($routeInfo['module']) ? self::$default_module : $routeInfo['module'];
		$controller = empty($routeInfo['controller']) ? self::$default_controller : $routeInfo['controller'];
		$action = empty($routeInfo['action']) ? self::$default_action : $routeInfo['action'];
		$viewDir = Yesf::app()->getConfig('application.dir') . 'modules/' . $module . '/views/';
		$yesf_response = new Response($response, $controller . '/' . $action, $viewDir);
		if (!empty($request->extension)) {
			$yesf_response->mimeType($request->extension);
		}
		//触发beforeDispatcher事件
		if (Plugin::trigger('beforeDispatcher', [$module, $controller, $action, $request, $yesf_response]) === NULL) {
			//如果$r非空，则代表结束当前请求
			if (($code = self::isValid($module, $controller, $action)) === Constant::ROUTER_VALID) {
				$controllerName = Yesf::getAppNamespace() . '\\controller\\' . $module . '\\' . ucfirst($controller);
				$actionName = $action . 'Action';
				try {
					$result = Swoole::call_user_func([$controllerName, $actionName], $request, $yesf_response);
				} catch (\Throwable $e) {
					$result = NULL;
					//触发失败事件
					Plugin::trigger('dispatchFailed', [$module, $controller, $action, $request, $yesf_response, $e]);
					//日志记录
					Logger::error('In request: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString());
				}
				//触发afterDispatcher事件
				if (($r = Plugin::trigger('afterDispatcher', [$module, $controller, $action, $request, $yesf_response, $result])) !== NULL) {
					$result = $r;
				}
			} else {
				if (Plugin::trigger('dispatchFailed', [$module, $controller, $action, $request, $yesf_response]) === NULL) {
					$yesf_response->disableView();
					$yesf_response->status(404);
					$yesf_response->write('Not Found');
				}
			}
		}
		unset($request, $response, $yesf_response);
		return $result;
	}
}