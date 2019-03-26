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
use \yesf\Swoole;
use \yesf\Config;
use \yesf\Logger;
use \yesf\http\Dispatcher;
use \yesf\http\Response;
use \yesf\database\Database;
use \yesf\exception\StartException;

if (!defined('YESF_ROOT')) {
	define('YESF_ROOT', __DIR__ . '/');
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
	throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_WARNING | E_USER_ERROR | E_USER_WARNING | E_DEPRECATED | E_USER_DEPRECATED);

class Yesf {
	/**
	 * 基本路径
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	protected static $_base_uri = '/';
	//缓存namespace
	protected static $_app_namespace = NULL;
	//单例化
	protected static $_instance = NULL;
	//运行环境，需要与配置文件中同名
	protected $_environment = 'product';
	//配置
	protected static $_config_project = NULL;
	protected static $_config_project_hash = '';
	protected static $_config_server = NULL;
	protected $_config = NULL;
	protected $_config_raw = NULL;
	/**
	 * 获取单例类
	 * 
	 * @access public
	 * @return object(Yesf)
	 */
	public static function app(): Yesf {
		if (self::$_instance === NULL) {
			throw new StartException('Yesf have not been construct yet');
		}
		return self::$_instance;
	}
	/**
	 * 实例化
	 * 
	 * @access public
	 * @param string/array/config $config 配置
	 */
	public function __construct() {
		self::$_instance = $this;
		//swoole检查
		if (!extension_loaded('swoole') && !defined('YESF_UNIT')) {
			throw new StartException('Extension "Swoole" is required');
		}
		//环境
		if (defined('APP_ENV')) {
			$this->_environment = APP_ENV;
		}
		if (!defined('APP_PATH')) {
			throw new StartException('You must define APP_PATH before initialize Yesf');
		}
		//配置检查
		if (!is_file(APP_PATH . 'config/Project.php')) {
			throw new StartException('Project configure file not found');
		}
		if (!is_file(APP_PATH . 'config/Server.php')) {
			throw new StartException('Server configure file not found');
		}
		//其他各项配置
		self::$_config_server = require(APP_PATH . 'config/Server.php');
		self::reloadProjectConfig();
		//获取Composer的Loader
		self::getLoader()->addPsr4(self::$_config_project['namespace'] . '\\model\\', APP_PATH . 'models');
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding(self::$_config_project['charset']);
		}
		if (extension_loaded('swoole')) {
			if (version_compare(SWOOLE_VERSION, '4.0.0', '<')) {
				throw new StartException('Yesf require Swoole 4.0 or later');
			}
			Swoole::init();
		}
	}
	/**
	 * 通过读取文件，获取Composer的Loader
	 * 
	 * @access public
	 * @return object(ClassLoader)
	 */
	public static function getLoader() {
		static $loader = NULL;
		if ($loader === NULL) {
			$classes = get_declared_classes();
			foreach ($classes as $clazz) {
				if (strpos($clazz, 'ComposerAutoloaderInit') === 0 && method_exists($clazz, 'getLoader')) {
					$loader = $clazz::getLoader();
					break;
				}
			}
			if ($loader === NULL) {
				throw new StartException('Composer loader not found');
			}
		}
		return $loader;
	}
	/**
	 * 将部分变量对外暴露，方便使用
	 * 
	 * @access public
	 */
	public static function getProjectConfig($key = NULL) {
		if ($key === NULL) {
			return self::$_config_project;
		} else {
			return isset(self::$_config_project[$key]) ? self::$_config_project[$key] : NULL;
		}
	}
	public static function reloadProjectConfig() {
		$hash = md5_file(APP_PATH . 'config/Project.php');
		if (self::$_config_project_hash === $hash) {
			return;
		}
		self::$_config_project_hash = $hash;
		self::$_config_project = require(APP_PATH . 'config/Project.php');
		self::$_app_namespace = self::$_config_project['namespace'];
		Dispatcher::init();
		Response::init();
	}
	public static function getServerConfig($key = NULL) {
		if ($key === NULL) {
			return self::$_config_server;
		} else {
			return isset(self::$_config_server[$key]) ? self::$_config_server[$key] : NULL;
		}
	}
	public function getConfig($key = NULL) {
		if ($key === NULL) {
			return $this->_config;
		} else {
			return $this->_config->get($key);
		}
	}
	public function setEnvironment($env) {
		$this->_environment = $env;
	}
	public function getEnvironment() {
		return $this->_environment;
	}
	public static function setBaseUri($uri) {
		self::$_base_uri = $uri;
	}
	public static function getBaseUri() {
		return self::$_base_uri;
	}
	public static function getAppNamespace() {
		return self::$_app_namespace;
	}
	/**
	 * Bootstrap
	 * 调用自定义的bootstrap，进行另外的一些初始化操作
	 * 
	 * @access public
	 */
	public function bootstrap() {
		$className = self::getProjectConfig('bootstrap');
		if (empty($className)) {
			$className = 'Bootstrap';
		}
		$classPath = APP_PATH . $className . '.php';
		if (is_file($classPath)) {
			require($classPath);
			$clazz = new $className;
			if (method_exists($clazz, 'run')) {
				$clazz->run();
			}
		}
		return $this;
	}
	/**
	 * 初始化完成，开始运行
	 * 
	 * @access public
	 */
	public function run($config) {
		$this->_config_raw = $config;
		Swoole::start();
	}
	/**
	 * 每次Woker启动时进行的初始化
	 * 用于各种需要被动态重载的内容
	 */
	public function initInWorker() {
		//配置
		if ((is_string($this->_config_raw) && is_file($this->_config_raw)) || is_array($this->_config_raw)) {
			$this->_config = new Config($this->_config_raw);
		} else {
			throw new StartException('Config can not be recognised');
		}
		self::reloadProjectConfig();
		Logger::init();
		Database::init();
		Response::initInWorker();
	}
}