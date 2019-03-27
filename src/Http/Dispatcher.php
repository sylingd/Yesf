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
use Yesf\DI\Container;
use Yesf\DI\GetEntryUtil;

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
		if (!in_array($module, self::$modules, TRUE)) {
			return self::ROUTE_ERR_MODULE;
		}
		$className = GetEntryUtil::controller($module, $controller);
		if (!Container::getInstance()->has($className)) {
			return self::ROUTE_ERR_CONTROLLER;
		}
		$clazz = Container::getInstance()->get($className);
		if (!method_exists($clazz, $action . 'Action')) {
			return self::ROUTE_ERR_ACTION;
		}
		return self::ROUTE_VALID;
	}
	/**
	 * Get module, controller and action
	 */
	private static function getRouteInfo($route) {
		$module = empty($routeInfo['module']) ? self::$default_module : ucfirst($routeInfo['module']);
		$controller = empty($routeInfo['controller']) ? self::$default_controller : ucfirst($routeInfo['controller']);
		$action = empty($routeInfo['action']) ? self::$default_action : ucfirst($routeInfo['action']);
		return [$module, $controller, $action];
	}
	/**
	 * 进行路由分发
	 * @codeCoverageIgnore
	 * @access public
	 * @param array $routeInfo 路由信息
	 * @param object $request 经过Yesf封装的请求内容
	 * @param object $res 来自Swoole的响应对象
	 * @return mixed
	 */
	public static function dispatch($routeInfo, $request, $res) {
		$result = NULL;
		list($module, $controller, $action) = self::getRouteInfo($routeInfo);
		$viewDir = APP_PATH . 'Modules/' . $module . '/View/';
		$response = new Response($res, $controller . '/' . $action, $viewDir);
		if (!empty($request->extension)) {
			$response->mimeType($request->extension);
		}
		$result = NULL;
		try {
			//触发beforeDispatcher事件
			$arr = [$module, $controller, $action, $request, $response];
			$is_continue = Plugin::trigger('beforeDispatcher', $arr);
			if ($is_continue === NULL) {
				$code = self::isValid($module, $controller, $action);
				if ($code === self::ROUTE_VALID) {
					$className = GetEntryUtil::controller($module, $controller);
					if (!Container::getInstance()->has($className)) {
						return self::ROUTE_ERR_CONTROLLER;
					}
					$clazz = Container::getInstance()->get($className);
					$actionName = $action . 'Action';
					$result = $clazz->$actionName($request, $response);
				} else {
					// Not found
					self::handleNotFound($module, $controller, $action, $request, $response);
				}
			}
		} catch (\Throwable $e) {
			$result = self::handleDispathException($module, $controller, $action, $request, $response, $e);
		}
		$response->end();
		unset($request, $response, $response);
		return $result;
	}
	private static function handleNotFound($module, $controller, $action, $request, $response) {
		$arr = [$module, $controller, $action, $request, $yesf_response];
		if (Plugin::trigger('dispatchFailed', $arr) === NULL) {
			$response->status(404);
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $module);
				$response->assign('controller', $controller);
				$response->assign('action', $action);
				$response->assign('code', $code);
				$response->assign('req', $request);
				$response->display(YESF_ROOT . 'Data/error_404_debug.php', TRUE);
			} else {
				$response->display(YESF_ROOT . 'Data/error_404.php', TRUE);
			}
		}
	}
	private static function handleDispathException($module, $controller, $action, $request, $response, $e) {
		//日志记录
		Logger::error('Uncaught exception: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString());
		//触发失败事件
		$arr = [$module, $controller, $action, $request, $response, $e];
		if (Plugin::trigger('dispatchFailed', $arr) === NULL) {
			//如果用户没有自行处理，输出默认模板
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $module);
				$response->assign('controller', $controller);
				$response->assign('action', $action);
				$response->assign('e', $e);
				$response->assign('req', $request);
				$response->display(YESF_ROOT . 'Data/error_debug.php', TRUE);
			} else {
				$response->display(YESF_ROOT . 'Data/error.php', TRUE);
			}
		}
		//触发afterDispatcher事件
		$arr = [$module, $controller, $action, $request, $response];
		return Plugin::trigger('afterDispatcher', $arr);
	}
}