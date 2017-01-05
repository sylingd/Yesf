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

namespace yesf\library\http;
use \yesf\Yesf;

class Router {
	protected static $rewrite = [];
	protected static $regex = [];
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
}