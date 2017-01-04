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
	public $baseUri = '/';
	//路由参数名称
	protected $routeParam = 'r';
	//Swoole实例类
	protected $server = NULL;
	//是否已经给HTTP请求绑定了处理方法
	protected $serverHttp = FALSE;
	/**
	 * 初始化
	 */
	public function app() {
		if (self::$_instance === NULL) {
			throw new \yesf\library\exception\StartException('Yesf have not been construct yet');
		}
		return self::$_instance;
	}
	public function __construct($config) {
		$this->init();
		//swoole检查
		if (!extension_loaded('swoole')) {
			throw new \yesf\library\exception\ExtensionNotFoundException('Extension "Swoole" is required', '10027');
		}
		//配置
		if (is_string($config) && is_file($config)) {
			$config = new \yesf\library\Config(parse_ini_file($config));
		} elseif (is_array($config)) {
			$config = new \yesf\library\Config($config);
		} else {
			throw new \yesf\library\exception\StartException('Config can not be recognised');
		}
		$config->replace('application.dir', APP_PATH);
		if ($config->has('application.namespace')) {
			$appNamespace = $config->get('application.namespace');
			Loader::registerNamespace($appNamespace . '\\controller', APP_PATH . 'controller/');
			Loader::registerNamespace($appNamespace . '\\model', APP_PATH . 'model/');
			Loader::registerNamespace($appNamespace . '\\module', APP_PATH . 'module/');
		}
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($config->get('application.charset'));
		}
		$this->server = new \swoole_http_server($config->get('swoole.ip'), $config->get('swoole.port')); 
		//基本事件
		$this->server->on('Start', ['\yesf\library\event\Server', 'eventStart']);
		$this->server->on('ManagerStart', ['\yesf\library\event\Server', 'eventManagerStart']);
		$this->server->on('WorkerStart', ['\yesf\library\event\Server', 'eventWorkerStart']);
		$this->server->on('WorkerError', ['\yesf\library\event\Server', 'eventWorkerError']);
		$this->server->on('Finish', ['\yesf\library\event\Server', 'eventFinish']);
		//完成初始化
		$this->config = $config;
		self::$_instance = $this;
	}
	/**
	 * 将部分变量对外暴露
	 */
	public function getConfig() {
		return $this->config;
	}
	public function setEnvironment($env) {
		$this->environment = $env;
	}
	public function setBaseUri($uri) {
		$this->baseUri = $uri;
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
		$bootstrap = $this->getConfig()->get('application.bootstrap');
		if (empty($bootstrap)) {
			$bootstrap = $this->getConfig()->get('application.dir') . 'Bootstrap.php';
		}
		if (is_file($bootstrap)) {
			require($bootstrap);
			$bootstrapClass ='Bootstrap';
			$bootstrapClass = new $bootstrapClass;
			call_user_func([$bootstrapClass, 'run']);
		}
		return $this;
	}
	public function run() {
		//判断是否已经给HTTP请求绑定了事件
		if (!$this->serverHttp) {
			$config = $this->getConfig()->get('swoole.http.advanced');
			$ssl = $this->getConfig()->get('swoole.http.ssl');
			if ($ssl['enable']) {
				$config['ssl_cert_file'] = $ssl['cert'];
				$config['ssl_key_file'] = $ssl['key'];
			}
			if ($this->config->get('swoole.http.http2')) {
				if (!isset($config['ssl_cert_file'])) {
					throw new \yesf\library\exception\StartException('Certfile not found');
				}
				$config['open_http2_protocol'] = TRUE;
			}
			if (!is_array($config['response_header'])) {
				$config['response_header'] = [];
			}
			if (!isset($config['response_header']['Content_Type'])) {
				$config['response_header']['Content_Type'] = 'application/html; charset=' . $this->config->get('application.charset');
			}
			$this->server->set($config);
			$this->server->on('Request', ['\yesf\library\event\HttpServer', 'eventRequest']);
			$this->server->on('Task', ['\yesf\library\event\Server', 'eventTask']);
			$this->serverHttp = TRUE;
		}
		$this->server->start();
	}
}
