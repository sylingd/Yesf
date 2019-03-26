<?php
/**
 * HTTP响应类
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
use Yesf\Config;
use Yesf\DI\Container;
use Yesf\Http\Vars as HttpVars;
use Yesf\Exception\InvalidClassException;

class Response {
	protected static $_tpl_auto_config = FALSE;
	//模板文件扩展名
	protected static $_tpl_extension = 'phtml';
	//模板引擎
	protected static $_tpl_engine = NULL;
	//模板变量
	protected $_tpl_vars = NULL;
	//模板目录
	protected $_tpl_path;
	//Swoole的Response
	protected $_sw_response = NULL;
	//是否自动渲染
	protected $_tpl_auto = NULL;
	//默认模板
	protected $_tpl_default = '';
	//模板引擎的实例化
	protected $_tpl_engine_obj = NULL;
	//Cookie相关配置
	protected static $cookie = [
		'expire' => -1,
		'path' => '/',
		'domain' => ''
	];
	/**
	 * 初始化函数
	 * 
	 * @access public
	 */
	public static function init() {
		$view_config = Yesf::getProjectConfig('view');
		self::$_tpl_auto_config = ($view_config['auto'] == 1) ? TRUE : FALSE;
		self::$_tpl_extension = ($view_config['extension'] ? $view_config['extension'] : 'phtml');
	}
	public static function initInWorker() {
		if (Yesf::app()->getConfig('cookie.expire')) {
			self::$cookie['expire'] = Yesf::app()->getConfig('cookie.expire');
		}
		if (Yesf::app()->getConfig('cookie.path')) {
			self::$cookie['path'] = Yesf::app()->getConfig('cookie.path');
		}
		if (Yesf::app()->getConfig('cookie.domain')) {
			self::$cookie['domain'] = Yesf::app()->getConfig('cookie.domain');
		}
	}
	public static function setTemplateEngine(string $classId) {
		$clazz = Container::getInstance()->get($classId);
		if (!is_subclass_of($clazz, __NAMESPACE__ . '\\TemplateInterface')) {
			throw new InvalidClassException("$clazz not implemented TemplateInterface");
		}
		self::$_tpl_engine = $classId;
	}
	/**
	 * 构建函数
	 * 
	 * @access public
	 * @param object $response Swoole的Response
	 * @param string $tpl_path 模板路径
	 */
	public function __construct($response, $tpl_default = NULL ,$tpl_path = NULL) {
		$this->_sw_response = $response;
		$this->_tpl_default = $tpl_default;
		if ($tpl_path === NULL) {
			$tpl_path = APP_PATH . 'views/';
		}
		$this->_tpl_path = $tpl_path;
		if (self::$_tpl_engine !== NULL) {
			$this->_tpl_engine_obj = Container::getInstance()->get(self::$_tpl_engine, TRUE);
		} else {
			$this->_tpl_vars = [];
		}
	}
	/**
	 * 设置模板路径
	 * 
	 * @access public
	 * @param string $tpl_path
	 */
	public function setTplPath($tpl_path) {
		$this->_tpl_path = $tpl_path;
	}
	/**
	 * 关闭模板自动渲染
	 * 
	 * @access public
	 */
	public function disableView() {
		$this->_tpl_auto = FALSE;
	}
	/**
	 * 将一个模板的渲染结果输出至浏览器
	 * 
	 * @access public
	 * @param string $tpl 模板路径
	 * @param boolean $is_abs_path 是否为绝对路径
	 */
	public function display($tpl, $is_abs_path = FALSE) {
		$data = $this->render($tpl, $is_abs_path);
		if (!empty($data)) $this->write($data);
	}
	/**
	 * 获取一个模板的渲染结果但不输出
	 * 
	 * @access public
	 * @param string $tpl 模板路径
	 * @param boolean $is_abs_path 是否为绝对路径
	 * @return string
	 */
	public function render($tpl, $is_abs_path = FALSE) {
		if ($is_abs_path) {
			$_tpl_full_path = $tpl;
		} else {
			$_tpl_full_path = $this->_tpl_path . $tpl . '.' . self::$_tpl_extension;
		}
		if (!$is_abs_path && $this->_tpl_engine_obj !== NULL) {
			return $this->_tpl_engine_obj->render($_tpl_full_path);
		}
		extract($this->_tpl_vars, EXTR_SKIP);
		ob_implicit_flush(FALSE);
		ob_start();
		if (is_file($_tpl_full_path)) {
			include($_tpl_full_path);
		}
		return ob_get_clean();
	}
	/**
	 * 将一个字符串输出至浏览器
	 * 
	 * @access public
	 * @param string $content 要输出的字符串
	 */
	public function write($content) {
		$this->_sw_response->write($content);
	}
	/**
	 * 发送一个文件
	 * 
	 * @access public
	 * @param string $filepath
	 * @param int $offset
	 * @param int $length
	 */
	public function sendfile($filepath, $offset, $length) {
		$this->_sw_response->sendfile($filepath, $offset, $length);
	}
	/**
	 * 注册一个模板变量
	 * 
	 * @access public
	 * @param string $k 名称
	 * @param mixed $v 值
	 */
	public function assign($k, $v) {
		if ($this->_tpl_engine_obj !== NULL) {
			$this->_tpl_engine_obj->assign($k, $v);
		} else {
			$this->_tpl_vars[$k] = $v;
		}
	}
	/**
	 * 清空模板变量
	 * 
	 * @access public
	 */
	public function clearAssign() {
		if ($this->_tpl_engine_obj !== NULL) {
			$this->_tpl_engine_obj->clearAssign();
		} else {
			$this->_tpl_vars[] = [];
		}
	}
	/**
	 * 向浏览器发送一个header信息
	 * 
	 * @access public
	 * @param string $k 名称
	 * @param mixed $v 值
	 */
	public function header($k, $v) {
		$this->_sw_response->header($k, $v);
	}
	/**
	 * 向浏览器发送一个状态码
	 * 
	 * @access public
	 * @param int $code
	 */
	public function status($code) {
		$this->_sw_response->status($code);
	}
	/**
	 * 设置Cookie
	 * 
	 * @access public
	 * @param array $param
	 * @param string $param[name] 名称
	 * @param string $param[value] 内容
	 * @param int $param[expire] 过期时间，-1为失效，0为SESSION，不传递为从config读取，其他为当前时间+$expire
	 * @param string $param[path] 若不传递，则从config读取
	 * @param string $param[domain] 若不传递，则从config读取
	 * @param boolean $param[https] 是否仅https传递，默认为否
	 * @param boolean $param[httponly] 是否为httponly
	 */
	public function cookie($param) {
		$name = $param['name'];
		//处理过期时间
		if (!isset($param['expire'])) {
			$expire = self::$cookie['expire'] === -1 ? 0 : time() + self::$cookie['expire'];
		} elseif ($param['expire'] === -1) {
			$expire = time() - 3600;
		} elseif ($param['expire'] === 0) {
			$expire = 0;
		} else {
			$expire = time() + $param['expire'];
		}
		//其他参数的处理
		!isset($param['path']) && $param['path'] = self::$cookie['path'];
		!isset($param['domain']) && $param['domain'] = self::$cookie['domain'];
		!isset($param['httponly']) && $param['httponly'] = FALSE;
		//HTTPS
		if (!isset($param['https'])) {
			$param['https'] = FALSE;
		}
		//设置
		$this->_sw_response->cookie($name, $param['value'], $expire, $param['path'], $param['domain'], $param['https'], $param['httponly']);
	}
	/**
	 * 发送mimeType的header
	 * 
	 * @access public
	 * @param string $extension 扩展名，例如JSON
	 */
	public function mimeType($extension) {
		$this->header('Content-Type', HttpVars::mimeType($extension));
	}
	/**
	 * 析构函数
	 * 
	 * @access public
	 */
	public function end() {
		$this->_tpl_vars = NULL;
		if ($this->_sw_response) {
			if (($this->_tpl_auto === NULL && self::$_tpl_auto_config) || $this->_tpl_auto) {
				$this->display($this->_tpl_default);
			}
			$this->_sw_response->end();
			$this->_sw_response = NULL;
		}
	}
	public function __destruct() {
		$this->end();
	}
}
