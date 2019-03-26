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

namespace Yesf\Http;
use Yesf\Yesf;
use Yesf\Plugin;
use Yesf\Logger;
use Yesf\Http\Response;

class Dispatcher {
	const ROUTE_VALID = 0;
	const ROUTE_ERR_MODULE = 1;
	const ROUTE_ERR_CONTROLLER = 2;
	const ROUTE_ERR_ACTION = 3;

	private static $modules = NULL;
	private static $default_module = 'index';
	private static $default_action = 'index';
	private static $default_controller = 'index';
	public static function init() {
		self::$modules = Yesf::getProjectConfig('modules');
		self::setDefaultModule(Yesf::getProjectConfig('module'));
	}
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
			if (!in_array($module, self::$modules, TRUE)) {
				return self::ROUTE_ERR_MODULE;
			}
			//判断controller是否存在
			$controllerPath = APP_PATH . 'modules/' . $module . '/controllers/' . $controller. '.php';
			if (!is_file($controllerPath)) {
				return self::ROUTE_ERR_CONTROLLER;
			}
			require($controllerPath);
			if (!class_exists($controllerName, FALSE)) {
				return self::ROUTE_ERR_CONTROLLER;
			}
		}
		if (!method_exists($controllerName, $action . 'Action')) {
			return self::ROUTE_ERR_ACTION;
		}
		return self::ROUTE_VALID;
	}
	/**
	 * 进行路由分发
	 * @codeCoverageIgnore
	 * @access public
	 * @param array $routeInfo 路由信息
	 * @param object $request 经过Yesf封装的请求内容
	 * @param object $response 来自Swoole的响应对象
	 * @return mixed
	 */
	public static function dispatch($routeInfo, $request, $response) {
		$result = NULL;
		$module = empty($routeInfo['module']) ? self::$default_module : $routeInfo['module'];
		$controller = empty($routeInfo['controller']) ? self::$default_controller : $routeInfo['controller'];
		$action = empty($routeInfo['action']) ? self::$default_action : $routeInfo['action'];
		$viewDir = APP_PATH . 'modules/' . $module . '/views/';
		$yesf_response = new Response($response, $controller . '/' . $action, $viewDir);
		if (!empty($request->extension)) {
			$yesf_response->mimeType($request->extension);
		}
		$result = NULL;
		try {
			//触发beforeDispatcher事件
			$arr = [$module, $controller, $action, $request, $yesf_response];
			$is_continue = Plugin::trigger('beforeDispatcher', $arr);
			if ($is_continue === NULL) {
				$code = self::isValid($module, $controller, $action);
				if ($code === self::ROUTE_VALID) {
					$actionName = $action . 'Action';
					$result = $controllerName::$actionName($request, $yesf_response);
				} else {
					// Not found
					$arr = [$module, $controller, $action, $request, $yesf_response];
					if (Plugin::trigger('dispatchFailed', $arr) === NULL) {
						$yesf_response->disableView();
						$yesf_response->status(404);
						if (Yesf::app()->getEnvironment() === 'develop') {
							$yesf_response->assign('module', $module);
							$yesf_response->assign('controller', $controller);
							$yesf_response->assign('action', $action);
							$yesf_response->assign('code', $code);
							$yesf_response->assign('req', $request);
							$yesf_response->display(YESF_ROOT . 'data/error_404_debug.php', TRUE);
						} else {
							$yesf_response->display(YESF_ROOT . 'data/error_404.php', TRUE);
						}
					}
				}
			}
		} catch (\Throwable $e) {
			$result = self::handleDispathException($module, $controller, $action, $request, $yesf_response, $e);
		}
		$yesf_response->end();
		unset($request, $response, $yesf_response);
		return $result;
	}
	private static function handleDispathException($module, $controller, $action, $request, $response, $e) {
		$response->disableView();
		//日志记录
		Logger::error('Uncaught exception: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString());
		//触发失败事件
		$arr = [$module, $controller, $action, $request, $response, $e];
		if (Plugin::trigger('dispatchFailed', $arr) === NULL) {
			//如果用户没有自行处理，输出默认模板
			$response->clearAssign();
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $module);
				$response->assign('controller', $controller);
				$response->assign('action', $action);
				$response->assign('e', $e);
				$response->assign('req', $request);
				$response->display(YESF_ROOT . 'data/error_debug.php', TRUE);
			} else {
				$response->display(YESF_ROOT . 'data/error.php', TRUE);
			}
		}
		//触发afterDispatcher事件
		$arr = [$module, $controller, $action, $request, $response];
		return Plugin::trigger('afterDispatcher', $arr);
	}
}