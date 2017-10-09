<?php
/**
 * HTTP事件回调
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
use \yesf\library\Router;

class HttpServer {
	//HTTP事件：收到请求
	public static function eventRequest($request, $response) {
		static $baseUri = NULL;
		static $baseUriLen = NULL;
		if ($baseUri === NULL) {
			$baseUri = Yesf::getBaseUri();
			$baseUriLen = strlen($baseUri);
		}
		if ('develop' === Yesf::app()->environment && function_exists('xdebug_start_trace')) {
			xdebug_start_trace();
		}
		//路由解析
		$uri = $request->server['request_uri'];
		if (strpos('?', $uri) !== FALSE) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		//去除开头的baseUri
		if (strpos($uri, $baseUri) === 0) {
			$uri = substr($uri, $baseUriLen);
		}
		//触发路由解析事件，转发至相应plugin
		$result = Plugin::trigger('routerStart', [$uri]);
		//如果plugin返回了解析结果，则终止默认的路由解析
		$request->extension = NULL;
		if (!is_array($result)) {
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
					$hasPoint = strrpos($uri, '.');
					if ($hasPoint !== FALSE) {
						$request->extension = substr($uri, $hasPoint + 1);
						$uri = substr($uri, 0, $hasPoint);
					}
				}
				//进行解析
				$routerType = Yesf::app()->getConfig('application.router.type');
				switch ($routerType) {
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
						break;
				}
				if (!is_array($result)) {
					$result = Router::parseMap($uri);
				}
			}
		} else {
			if (isset($result[3])) {
				$request->extension = $result[3];
			}
		}
		$request->param = $result[0];
		//开始路由分发
		Router::route($result[1], $request, $response);
		unset($request, $response, $yesfResponse, $result);
	}
}