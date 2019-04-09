<?php
/**
 * 路由解析类
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
use Yesf\Http\Response;

class Router implements RouterInterface {
	/**
	 * 基本路径
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	protected $prefix = '/';
	protected $rewrite = [];
	protected $regex = [];
	protected $router = null;
	protected $module = 'index';
	public function __construct() {
		$this->module = Yesf::app()->getConfig('index', Yesf::CONF_PROJECT);
		$this->router = Yesf::app()->getConfig('router', Yesf::CONF_PROJECT);
	}
	public function setPrefix($prefix = '/') {
		$this->prefix = $prefix;
	}
	/**
	 * 按照Map方式解析路由
	 * 对于请求request_uri为"/ap/foo/bar"
	 * base_uri为"/ap"
	 * 则最后参加路由的request_uri为"/foo/bar"
	 */
	public function parseMap(Request $request) {
		//解析
		$res = explode('/', $request->uri, 3);
		if (count($res) === 3) {
			$request->module = $res[0];
			$request->controller = $res[1];
			$request->action = $res[2];
		} else {
			$request->module = $this->module;
			$request->controller = $res[0];
			$request->action = $res[1];
		}
		return true;
	}
	/**
	 * 按照Rewrite方式解析路由
	 */
	public function parseRewrite(Request $request) {
		foreach ($this->rewrite as $rewrite) {
			if (preg_match($rewrite['regex'], $request->uri, $matches)) {
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
			$request->param = $param;
			$request->module = isset($dispatch['module']) ? $dispatch['module'] : $this->module;
			$request->controller = $dispatch['controller'];
			$request->action = $dispatch['action'];
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 添加Rewrite解析规则
	 * @param string $rule 基本规则
	 * @param array $dispatch 分发规则
	 */
	public function addRewrite($rule, $dispatch) {
		//将规则解析为正则
		$param = [];
		$regex = str_replace('/', '\\/', $rule);
		$regex = preg_replace_callback('/:([a-zA-Z0-9_]+)/', function($matches) use (&$param) {
			$param[] = $matches[1];
			return '([^\/]+)';
		}, $regex);
		//处理结尾的星号
		if (substr($regex, -1, 1) === '*') {
			$regex = preg_replace('/\*$/', '(.*?)', $regex);
		}
		$regex = '/^' . $regex . '$/';
		$this->rewrite[] = [
			'regex' => $regex,
			'rule' => $rule,
			'param' => $param,
			'dispatch' => $dispatch
		];
	}
	/**
	 * 按照Regex方式解析路由
	 */
	public function parseRegex(Request $request) {
		foreach ($this->regex as $regex) {
			if (preg_match($regex['regex'], $request->uri, $matches)) {
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
			$request->param = $param;
			$request->module = isset($dispatch['module']) ? $dispatch['module'] : $this->module;
			$request->controller = $dispatch['controller'];
			$request->action = $dispatch['action'];
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 添加Regex解析规则
	 * @param string $rule 规则
	 * @param array $dispatch 分发规则
	 * @param array $param 参数列表
	 */
	public function addRegex($rule, $dispatch, $param) {
		$this->regex[] = [
			'regex' => $rule,
			'param' => $param,
			'dispatch' => $dispatch
		];
	}
	public function parse(Request $request) {
		$len = strlen($this->prefix);
		//路由解析
		$uri = $request->server['request_uri'];
		if (strpos('?', $uri) !== false) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		//去除开头的baseUri
		if (strpos($uri, $this->prefix) === 0) {
			$uri = substr($uri, $len);
		}
		//为空则读取默认设置
		if (empty($uri)) {
			$request->module = $this->module;
			$request->controller = 'index';
			$request->action = 'index';
			return;
		}
		$request->uri = $uri;
		//扩展名自动处理
		if (Yesf::app()->getConfig('router.extension', Yesf::CONF_PROJECT)) {
			$hasPoint = strrpos($uri, '.');
			if ($hasPoint !== false) {
				$request->extension = substr($uri, $hasPoint + 1);
				$uri = substr($uri, 0, $hasPoint);
			}
		}
		// Try
		if ($this->parseRewrite($request)) {
			return;
		}
		if ($this->parseRegex($request)) {
			return;
		}
		$this->parseMap($request);
	}
}