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

use SessionHandlerInterface;
use Yesf\Yesf;
use Yesf\Plugin;
use Yesf\Logger;
use Yesf\DI\Container;
use Yesf\DI\GetEntryUtil;
use Yesf\Exception\NotFoundException;

class Dispatcher {
	const ROUTE_VALID = 0;
	const ROUTE_ERR_MODULE = 1;
	const ROUTE_ERR_CONTROLLER = 2;
	const ROUTE_ERR_ACTION = 3;

	/** @var RouterInterface $router Router */
	private $router;

	/** @var SessionHandlerInterface $session_handler Session Handler */
	private $session_handler;

	/** @var array $modules Avaliable modules */
	private $modules;

	/** @var bool $static_enable Enable static handler */
	private $static_enable = false;
	/** @var string $static_prefix Static files url prefix */
	private $static_prefix = '';
	/** @var string $static_dir Static directory */
	private $static_dir = '';

	public function __construct(Router $router, SessionHandlerInterface $session) {
		$this->router = $router;
		$this->session_handler = $session;
		$this->modules = Yesf::app()->getConfig('modules', Yesf::CONF_PROJECT);

		$static = Yesf::app()->getConfig('static', Yesf::CONF_PROJECT);
		if ($static === true || (is_array($static) && $static['enable'])) {
			$this->static_enable = true;
			$this->static_prefix = isset($static['prefix']) ? $static['prefix'] : '/';
			$this->static_dir = isset($static['dir']) ? str_replace('@APP', APP_PATH, $static['dir']) : APP_PATH . '/Static';
			if (!is_dir($this->static_dir)) {
				throw new NotFoundException("Directory {$this->static_dir} not exists");
			}
			$this->static_dir = str_replace('\\', '/', $this->static_dir);
			if (substr($this->static_dir, -1) !== '/') {
				$this->static_dir .= '/';
			}
		}
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
	 * 
	 * @access public
	 * @param RouterInterface $router Router
	 */
	public function setRouter(RouterInterface $router) {
		$this->router = $router;
	}
	/**
	 * Set session handler
	 * 
	 * @access public
	 * @param SessionHandlerInterface $handler Session handler
	 */
	public function setSessionHandler(SessionHandlerInterface $handler) {
		$this->session_handler = $handler;
		$handler->open('', '');
	}
	/**
	 * Get session handler
	 * 
	 * @access public
	 */
	public function getSessionHandler() {
		return $this->session_handler;
	}
	/**
	 * Handle http request
	 * 
	 * @access public
	 * @param Request $req Request
	 * @param Response $res Response
	 */
	public function handleRequest(Request $req, Response $res) {
		if ($this->static_enable) {
			$uri = $req->server['request_uri'];
			if (strpos($uri, $this->static_prefix) === 0) {
				$uri = substr($uri, strlen($this->static_prefix));
			}
			$path = realpath($this->static_dir . $uri);
			if ($path !== false && strpos($path, $this->static_dir) === 0) {
				if (Plugin::trigger('beforeStatic', [$path, $request, $response]) === null) {
					$res->mimeType(pathinfo($path, PATHINFO_EXTENSION));
					$res->sendfile($path);
				}
				Plugin::trigger('afterStatic', [$path, $request, $response]);
				return;
			}
		}
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
	 * 
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
		$request->end();
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