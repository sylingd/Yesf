<?php

/**
 * 主操作类
 * 涉及到Swoole的主要操作均通过此类进行
 * Swoole版本需求：1.8.6+
 * 仅当运行于HttpServer模式时支持
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\event;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\Plugin;
use \yesf\library\http\Router;
use \yesf\library\http\Response;

class HttpServer {
	//HTTP事件：收到请求
	public static function eventRequest($request, $response) {
		//根据设置，分配重写规则
		if ('develop' === Yesf::app()->environment && function_exists('xdebug_start_trace')) {
			xdebug_start_trace();
		}
		//路由解析
		$uri = $request->server['request_uri'];
		if (strpos('?', $uri) !== FALSE) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		//去除开头的baseUri
		$baseUri = Yesf::app()->getBaseUri();
		$uri = ltrim($uri, $baseUri);
		//触发路由解析事件，转发至相应plugin
		$result = Plugin::trigger('routerStartup', [$uri]);
		//如果plugin返回了解析结果，则终止默认的路由解析
		if (!is_array($result)) {
			$request->extension = NULL;
			//为空则读取默认设置
			if (empty($uri)) {
				$result = [[], [
					'module' => Yesf::app()->getConfig('application.module'),
					'controller' => 'index',
					'action' => 'index'
				]];
			} else {
				//扩展名自动处理
				if (Yesf::app()->getConfig('application.router.extension')) {
					preg_match('/\.(\w+)$/', '', $uri, $matches);
					$request->extension = $matches[1];
					$uri = preg_replace('/\.(\w+)$/', '', $uri);
				}
				//进行解析
				switch (Yesf::app()->getConfig('application.router.type')) {
					case 'map':
						$result = Router::parseMap($uri);
						break;
					case 'regex':
						$result = Router::parseRegex($uri);
						break;
					case 'rewrite':
						$result = Router::parseRewrite($uri);
						break;
					default:
						$result = Router::parseMap($uri);
						return;
				}
				if (!is_array($result)) {
					$result = Router::parseMap($uri);
				}
			}
		}
		$request->param = $result[0];
		//开始路由分发
		$module = isset($result[1]['module']) ? $result[1]['module'] : Yesf::app()->getConfig()->get('application.module');
		$controller = $result[1]['controller'];
		$action = $result[1]['action'];
		$viewDir = Yesf::app()->getConfig('application.dir') . 'modules/' . $module . '/views/';
		$yesfResponse = new Response($response, $controller . '/' . $action, $viewDir);
		if (!empty($request->extension)) {
			$yesfResponse->mimeType($request->extension);
		}
		if (($code = Router::isValid($module, $controller, $action)) === Constant::ROUTER_VALID) {
			$controllerName = Yesf::app()->getConfig('application.namespace') . '\\controller\\' . $controller;
			call_user_func([$controllerName, $action . 'Action'], $request, $yesfResponse);
			unset($request, $yesfResponse, $result);
		} else {
			$yesfResponse->status(404);
		}
	}
}