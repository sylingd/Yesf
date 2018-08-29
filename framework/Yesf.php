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
	 * 基本路径
	 * 在进行路由解析时会忽略此前缀。默认为/，即根目录
	 * 一般不会有此需要，仅当程序处于网站二级目录时会用到
	 */
	protected static $baseUri = '/';
	//缓存namespace
	protected static $_app_namespace = NULL;
	//单例化
	protected static $_instance = NULL;
	//运行环境，需要与配置文件中同名
	public $environment = 'product';
	//配置
	protected $config = NULL;
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
	public function __construct($config) {
		self::$_instance = $this;
		//swoole检查
		if (!extension_loaded('swoole') && !defined('YESF_UNIT')) {
			throw new StartException('Extension "Swoole" is required');
		}
		//环境
		if (defined('APP_ENV')) {
			$this->environment = APP_ENV;
		}
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
	 * Bootstrap
	 * 调用自定义的bootstrap，进行另外的一些初始化操作
	 * 
	 * @access public
	 */
	public function bootstrap() {
		$className = $this->getConfig('application.bootstrap');
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
	public function run() {
		Swoole::start();
	}
}