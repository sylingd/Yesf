<?php
/**
 * HTTP事件回调
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace Yesf\Event;
use Yesf\Yesf;
use Yesf\Constant;
use Yesf\Plugin;
use Yesf\Http\Request;
use Yesf\Http\Router;
use Yesf\Http\Dispatcher;

class HttpServer {
	protected static $router = NULL;
	protected static $module = 'index';
	public static function init() {
		self::$module = Yesf::getProjectConfig('index');
		self::$router = Yesf::getProjectConfig('router');
	}
	//HTTP事件：收到请求
	public static function eventRequest($request, $response) {
		$baseUri = Yesf::getBaseUri();
		$baseUriLen = strlen($baseUri);
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
		$yesfRequest = new Request($request);
		$yesfRequest->request_uri = $uri;
		if (!is_array($result)) {
			//为空则读取默认设置
			if (empty($uri)) {
				$result = [[], [
					'module' => self::$module,
					'controller' => 'index',
					'action' => 'index'
				]];
			} else {
				//扩展名自动处理
				if (self::$router['extension']) {
					$hasPoint = strrpos($uri, '.');
					if ($hasPoint !== FALSE) {
						$yesfRequest->extension = substr($uri, $hasPoint + 1);
						$uri = substr($uri, 0, $hasPoint);
					}
				}
				//进行解析
				switch (self::$router['type']) {
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
				$yesfRequest->extension = $result[3];
			}
		}
		$yesfRequest->param = $result[0];
		//开始路由分发
		Dispatcher::dispatch($result[1], $yesfRequest, $response);
		unset($request, $response, $result, $yesfRequest);
	}
}