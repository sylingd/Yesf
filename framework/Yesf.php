<?php
/**
 * 基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf;

use yesf\library\Loader;

if (!defined('YESF_ROOT')) {
	define('YESF_ROOT', __DIR__ . '/');
}

class Yesf {
	/**
	 * 运行环境，需要与配置文件中同名
	 * 其中，设置为develop时，会自动打开一些调试功能
	 */
	public $environment = 'product';
	/**
	 * 基本目录
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	protected $baseUri = '/';
	//路由参数名称
	protected $routeParam = 'r';
	//Swoole实例类
	protected $server = NULL;
	//是否已经给HTTP请求绑定了处理方法
	protected $serverHttp = FALSE;
	//单例化
	protected static $_instance = NULL;
	/**
	 * 初始化
	 */
	public static function app() {
		if (self::$_instance === NULL) {
			throw new \yesf\library\exception\StartException('Yesf have not been construct yet');
		}
		return self::$_instance;
	}
	public function __construct($config) {
		$this->init();
		//swoole检查
		if (!extension_loaded('swoole') && !defined('YESF_UNIT')) {
			throw new \yesf\library\exception\ExtensionNotFoundException('Extension "Swoole" is required', '10027');
		}
		self::$_instance = $this;
		//配置
		if ((is_string($config) && is_file($config)) || is_array($config)) {
			$config = new \yesf\library\Config($config);
		} else {
			throw new \yesf\library\exception\StartException('Config can not be recognised');
		}
		$config->replace('application.dir', APP_PATH);
		Loader::registerNamespace($config->get('application.namespace') . '\\model', APP_PATH . 'model/');
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($config->get('application.charset'));
		}
		if (extension_loaded('swoole')) {
			$this->server = new \swoole_http_server($config->get('swoole.ip'), $config->get('swoole.port')); 
			//基本事件
			$this->server->on('Start', ['\yesf\library\event\Server', 'eventStart']);
			$this->server->on('ManagerStart', ['\yesf\library\event\Server', 'eventManagerStart']);
			$this->server->on('WorkerStart', ['\yesf\library\event\Server', 'eventWorkerStart']);
			$this->server->on('WorkerError', ['\yesf\library\event\Server', 'eventWorkerError']);
			$this->server->on('Finish', ['\yesf\library\event\Server', 'eventFinish']);
		}
		//完成初始化
		$this->config = $config;
	}
	/**
	 * 将部分变量对外暴露
	 */
	public function getConfig($key = NULL) {
		if ($key === NULL) {
			return $this->config;
		} else {
			return $this->config->get($key);
		}
	}
	public function setEnvironment($env) {
		$this->environment = $env;
	}
	public function setBaseUri($uri) {
		$this->baseUri = $uri;
	}
	public function getBaseUri() {
		return $this->baseUri;
	}
	/**
	 * 以下是各个过程的事件
	 */
	protected function init() {
		//注册自动加载
		if (!class_exists('yesf\\library\\Loader', FALSE)) {
			require(YESF_ROOT . 'library/Loader.php');
		}
		Loader::register();
	}
	public function bootstrap() {
		$bootstrapClass = $this->getConfig('application.bootstrap');
		if (empty($bootstrapClass)) {
			$bootstrapClass = 'Bootstrap';
		}
		$bootstrap = APP_PATH . $bootstrapClass . '.php';
		if (is_file($bootstrap)) {
			require($bootstrap);
			$bootstrapClass = new $bootstrapClass;
			call_user_func([$bootstrapClass, 'run']);
		}
		return $this;
	}
	public function run() {
		//判断是否已经给HTTP请求绑定了事件
		if (!$this->serverHttp) {
			$config = $this->getConfig('swoole.http.advanced');
			$ssl = $this->getConfig('swoole.http.ssl');
			if ($ssl['enable']) {
				$config['ssl_cert_file'] = $ssl['cert'];
				$config['ssl_key_file'] = $ssl['key'];
			}
			if ($this->getConfig('swoole.http.http2')) {
				if (!isset($config['ssl_cert_file'])) {
					throw new \yesf\library\exception\StartException('Certfile not found');
				}
				$config['open_http2_protocol'] = TRUE;
			}
			if (!is_array($config['response_header'])) {
				$config['response_header'] = [];
			}
			if (!isset($config['response_header']['Content_Type'])) {
				$config['response_header']['Content_Type'] = 'application/html; charset=' . $this->getConfig('application.charset');
			}
			$this->server->set($config);
			$this->server->on('Request', ['\yesf\library\event\HttpServer', 'eventRequest']);
			$this->server->on('Task', ['\yesf\library\event\Server', 'eventTask']);
			$this->serverHttp = TRUE;
		}
		$this->server->start();
	}
}
