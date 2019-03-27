<?php
namespace YesfApp\library;

use Yesf\Plugin;
use Yesf\Logger;

class PluginHandler {
	public static function register() {
		Plugin::register('routerStart', [__CLASS__, 'onRouterStart']);
		Plugin::register('workerStart', [__CLASS__, 'onWorkerStart']);
		Plugin::register('beforeDispatcher', [__CLASS__, 'onBeforeDispatcher']);
		Plugin::register('dispatchFailed', [__CLASS__, 'onDispatchFailed']);
		Plugin::register('afterDispatcher', [__CLASS__, 'onAfterDispatcher']);
	}
	public static function onRouterStart($url) {
		// Parse some special urls
		if ($url === '/alipay/notify') {
			return [
				[
				  	'module' => 'index',
				  	'controller' => 'pay',
				  	'action' => 'notify'
				], [
				  	'type' => 'alipay'
				], 'html'
			];
		}
		// Normal URL, Parse by default
		return null;
	}
	public static function onWorkerStart() {
		// Worker start
	}
	public static function onBeforeDispatcher($module, $controller, $action, $request, $response) {
		// Set Content-Type to json when access api module
		if ($module === 'api') {
			$response->header('Content-Type', 'application/json; charset=UTF-8');
		}
		// If module is admin, check login statue
		if ($module === 'admin') {
			if (!$request->cookie['password'] || $request->cookie['password'] !== 'password') {
				$response->write('Forbidden');
				// End the default distribution
				return true;
			}
		}
		// If method is POST, check the csrf_token
		if ($request->server['request_method'] === 'POST') {
			if (!isset($request->post['_csrf_token']) || empty($request->post['_csrf_token']) || $request->post['_csrf_token'] !== $request->cookie['_csrf_token']) {
				$response->write('Forbidden');
				// End the default distribution
				return true;
			}
		}
		// Continue the default distribution
		return null;
	}
	public static function onDispatchFailed($module, $controller, $action, $request, $response, $exception = null) {
		// If an exception occurs during distribution
		if ($exception !== null) {
			Logger::error($exception->getMessage());
			$response->write('Something is wrong');
		} else {
			// 404 Not Found
			$response->write('404 Not Found');
		}
		// I've dealt with it myself
		return true;
	}
	public static function onAfterDispatcher($module, $controller, $action, $request, $response, $result) {
		// After dispatcher
	}
}