<?php
/**
 * 基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf;
use \yesf\library\Swoole;
use \yesf\library\Config;
use \yesf\library\http\Dispatcher;
use \yesf\library\http\Response;
use \yesf\library\database\Database;
use \yesf\library\exception\StartException;

if (!defined('YESF_ROOT')) {
	define('YESF_ROOT', __DIR__ . '/');
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
	throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_WARNING | E_USER_ERROR | E_USER_WARNING | E_DEPRECATED | E_USER_DEPRECATED);

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
	protected static $baseUri = '/';
	//路由参数名称
	protected $routeParam = 'r';
	//配置
	protected $config = NULL;
	//缓存namespace
	protected static $_app_namespace = NULL;
	//单例化
	protected static $_instance = NULL;
	/**
	 * 初始化
	 */
	public static function app() {
		if (self::$_instance === NULL) {
			throw new StartException('Yesf have not been construct yet');
		}
		return self::$_instance;
	}
	public function __construct($config) {
		$this->init();
		//swoole检查
		if (!extension_loaded('swoole') && !defined('YESF_UNIT')) {
			throw new StartException('Extension "Swoole" is required');
		}
		self::$_instance = $this;
		//配置
		if ((is_string($config) && is_file($config)) || is_array($config)) {
			$config = new Config($config);
		} else {
			throw new StartException('Config can not be recognised');
		}
		$config->replace('application.dir', APP_PATH);
		$this->config = $config;
		self::$_app_namespace = $config->get('application.namespace');
		//获取Composer的Loader
		self::getLoader()->addPsr4($config->get('application.namespace') . '\\model\\', APP_PATH . 'models');
		Dispatcher::setDefaultModule($config->get('application.module'));
		Response::_init($config);
		Database::_init($config);
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding($config->get('application.charset'));
		}
		if (extension_loaded('swoole')) {
			if (version_compare(SWOOLE_VERSION, '4.0.0', '<')) {
				throw new StartException('Yesf require Swoole 4.0 or later');
			}
			Swoole::init();
			Swoole::initConsole();
		}
	}
	/**
	 * 通过读取文件，获取Composer的Loader
	 * @access public
	 * @return object(ClassLoader)
	 */
	public static function getLoader() {
		static $loader = NULL;
		if ($loader === NULL) {
			$files = get_included_files();
			$composer_file = NULL;
			foreach ($files as $f) {
				if (strpos($f, 'composer/autoload_real.php') !== FALSE || strpos($f, 'composer\\autoload_real.php') !== FALSE) {
					$composer_file = $f;
					break;
				}
			}
			if ($composer_file === NULL) {
				throw new StartException('Composer loader not found');
			}
			//读取文件
			$filecontent = file_get_contents($f);
			preg_match('/class ComposerAutoloaderInit(\w+)/', $filecontent, $matches);
			$className = 'ComposerAutoloaderInit' . $matches[1];
			$loader = $className::getLoader();
		}
		return $loader;
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
	public static function setBaseUri($uri) {
		self::$baseUri = $uri;
	}
	public static function getBaseUri() {
		return self::$baseUri;
	}
	public static function getAppNamespace() {
		return self::$_app_namespace;
	}
	/**
	 * 以下是各个过程的事件
	 */
	protected function init() {
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
			if (method_exists($bootstrapClass, 'run')) {
				$bootstrapClass->run();
			}
		}
		return $this;
	}
	public function run() {
		Swoole::start();
	}
}