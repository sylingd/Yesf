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

namespace Yesf;
use Yesf\Swoole;
use Yesf\Config;
use Yesf\DI\Container;
use Yesf\Exception\StartException;
use Yesf\Exception\NotFoundException;
use Yesf\Exception\RequirementException;

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
	protected static $base_uri = '/';
	//缓存namespace
	protected static $app_namespace = null;
	//单例化
	protected static $instance = null;
	//运行环境，需要与配置文件中同名
	protected $environment = 'product';
	//配置
	protected static $config_project = null;
	protected static $config_project_hash = '';
	protected static $config_server = null;
	protected $config = null;
	protected $config_raw = null;
	/**
	 * 获取单例类
	 * 
	 * @access public
	 * @return object(Yesf)
	 */
	public static function app(): Yesf {
		if (self::$instance === null) {
			throw new StartException('Yesf have not been construct yet');
		}
		return self::$instance;
	}
	/**
	 * 实例化
	 * 
	 * @access public
	 * @param string/array/config $config 配置
	 */
	public function __construct() {
		self::$instance = $this;
		//swoole检查
		if (!extension_loaded('swoole') && !defined('YESF_UNIT')) {
			throw new RequirementException('Extension "Swoole" is required');
		}
		//环境
		if (defined('APP_ENV')) {
			$this->environment = APP_ENV;
		}
		if (!defined('APP_PATH')) {
			throw new StartException('You must define APP_PATH before initialize Yesf');
		}
		//配置检查
		if (!is_file(APP_PATH . 'Config/Project.php')) {
			throw new NotFoundException('Project configure file not found');
		}
		if (!is_file(APP_PATH . 'Config/Server.php')) {
			throw new NotFoundException('Server configure file not found');
		}
		//其他各项配置
		self::$config_server = require(APP_PATH . 'Config/Server.php');
		self::reloadProjectConfig();
		//将APP的namespace添加到Autoload
		self::addAppToLoader();
		//编码相关
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding(self::$config_project['charset']);
		}
		if (extension_loaded('swoole')) {
			if (version_compare(SWOOLE_VERSION, '4.0.0', '<')) {
				throw new RequirementException('Yesf require Swoole 4.0 or later');
			}
			Swoole::init();
		}
	}
	/**
	 * 将APP的namespace添加到Autoload
	 */
	private static function addAppToLoader() {
		$namespace = self::$config_project['namespace'];
		if (strpos('\\', $namespace) === false) {
			$namespace .= '\\';
		}
		self::getLoader()->addPsr4($namespace, APP_PATH);
	}
	/**
	 * 通过读取文件，获取Composer的Loader
	 * 
	 * @access public
	 * @return object(ClassLoader)
	 */
	public static function getLoader() {
		static $loader = null;
		if ($loader === null) {
			$classes = get_declared_classes();
			foreach ($classes as $clazz) {
				if (strpos($clazz, 'ComposerAutoloaderInit') === 0 && method_exists($clazz, 'getLoader')) {
					$loader = $clazz::getLoader();
					break;
				}
			}
			if ($loader === null) {
				throw new RequirementException('Composer loader not found');
			}
		}
		return $loader;
	}
	/**
	 * 将部分变量对外暴露，方便使用
	 * 
	 * @access public
	 */
	public function loadEnvConfig() {
		if ((is_string($this->config_raw) && is_file($this->config_raw)) || is_array($this->config_raw)) {
			$this->config = new Config($this->config_raw);
		} else {
			throw new NotFoundException('Config can not be recognised');
		}
	}
	public static function getProjectConfig($key = null) {
		if ($key === null) {
			return self::$config_project;
		} else {
			return isset(self::$config_project[$key]) ? self::$config_project[$key] : null;
		}
	}
	public static function loadProjectConfig() {
		$hash = md5_file(APP_PATH . 'Config/Project.php');
		if (self::$config_project_hash === $hash) {
			return;
		}
		self::$config_project_hash = $hash;
		self::$config_project = require(APP_PATH . 'Config/Project.php');
		self::$app_namespace = self::$config_project['namespace'];
	}
	public static function getServerConfig($key = null) {
		if ($key === null) {
			return self::$config_server;
		} else {
			return isset(self::$config_server[$key]) ? self::$config_server[$key] : null;
		}
	}
	public function getConfig($key = null) {
		if ($key === null) {
			return $this->config;
		} else {
			return $this->config->get($key);
		}
	}
	public function setEnvironment($env) {
		$this->environment = $env;
	}
	public function getEnvironment() {
		return $this->environment;
	}
	public static function setBaseUri($uri) {
		self::$base_uri = $uri;
	}
	public static function getBaseUri() {
		return self::$base_uri;
	}
	public static function getAppNamespace() {
		return self::$app_namespace;
	}
	/**
	 * Bootstrap
	 * 调用自定义的bootstrap，进行另外的一些初始化操作
	 * 
	 * @access public
	 */
	public function bootstrap() {
		$container = Container::getInstance();
		$className = self::getProjectConfig('namespace') . '\\Bootstrap';
		if ($container->has($className)) {
			$clazz = $container->get($className);
			$clazz->run();
		}
		return $this;
	}
	/**
	 * 初始化完成，开始运行
	 * 
	 * @access public
	 */
	public function run($config) {
		$this->config_raw = $config;
		Swoole::start();
	}
}