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

	private $router;
	private $modules;
	public function __construct(Router $router) {
		$this->router = $router;
		$this->modules = Yesf::getProjectConfig('modules');
	}
	/**
	 * 判断路由是否合法
	 * @codeCoverageIgnore
	 * @param string $module
	 * @param string $controller
	 * @param string $action
	 * @return int
	 */
	public function isValid($module, $controller, $action) {
		if (!in_array($module, $this->modules, true)) {
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
	 * Set router
	 */
	public function setRouter(RouterInterface $router) {
		$this->router = $router;
	}
	public function handleRequest(Request $req, Response $res) {
		if (Plugin::trigger('beforeRoute', [$uri]) === null) {
			$this->router->parse($req);
		}
		$result = null;
		//触发beforeDispatcher事件
		if (Plugin::trigger('beforeDispatch', [$request, $response]) === null) {
			$result = $this->dispatch($req, $res);
		}
		//触发afterDispatcher事件
		Plugin::trigger('afterDispatch', [$request, $response, $result]);
	}
	/**
	 * 进行路由分发
	 * @codeCoverageIgnore
	 * @access public
	 * @param array $routeInfo 路由信息
	 * @param object $request 请求内容
	 * @param object $response 响应对象
	 * @return mixed
	 */
	public function dispatch(Request $request, Response $response) {
		$result = null;
		$module = ucfirst($request->module);
		$controller = ucfirst($request->controller);
		$action = ucfirst($request->action);
		$response->setTemplate($controller . '/' . $action);
		$response->setTemplatePath(APP_PATH . 'Module/' . $module . '/View/');
		if (!empty($request->extension)) {
			$response->mimeType($request->extension);
		}
		$result = null;
		try {
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
				self::handleNotFound($request, $response);
			}
		} catch (\Throwable $e) {
			$result = self::handleDispathException($request, $response, $e);
		}
		$response->end();
		unset($request, $response);
		return $result;
	}
	private static function handleNotFound($request, $response) {
		$arr = [$request, $yesf_response];
		if (Plugin::trigger('dispatchFailed', $arr) === null) {
			$response->status(404);
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $request->module);
				$response->assign('controller', $request->controller);
				$response->assign('action', $request->action);
				$response->assign('code', $code);
				$response->assign('request', $request);
				$response->display(YESF_ROOT . 'Data/error_404_debug.php', true);
			} else {
				$response->display(YESF_ROOT . 'Data/error_404.php', true);
			}
		}
	}
	private static function handleDispathException($request, $response, $exception) {
		//日志记录
		Logger::error('Uncaught exception: ' . $e->getMessage() . '. Trace: ' . $e->getTraceAsString());
		//触发失败事件
		$arr = [$request, $response, $exception];
		if (Plugin::trigger('dispatchFailed', $arr) === null) {
			//如果用户没有自行处理，输出默认模板
			$response->disableView();
			$response->setCurrentTemplateEngine(Template::class);
			if (Yesf::app()->getEnvironment() === 'develop') {
				$response->assign('module', $request->module);
				$response->assign('controller', $request->controller);
				$response->assign('action', $request->action);
				$response->assign('exception', $exception);
				$response->assign('request', $request);
				$response->display(YESF_ROOT . 'Data/error_debug.php', true);
			} else {
				$response->display(YESF_ROOT . 'Data/error.php', true);
			}
		}
	}
}