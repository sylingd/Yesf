<?php
/**
 * HTTP响应类
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
use \yesf\library\http\Vars as HttpVars;

class Response {
	public static $_tpl_auto_config = TRUE;
	//模板文件扩展名
	public static $_tpl_extension = 'phtml';
	//模板变量
	protected $_tpl_vars = [];
	//模板目录
	protected $_tpl_path;
	//Swoole的Response
	protected $_sw_response = NULL;
	//是否自动渲染
	protected $_tpl_auto = NULL;
	//默认模板
	protected $_tpl_default = '';
	/**
	 * 构建函数
	 * @param object $response Swoole的Response
	 * @param string $tpl_path 模板路径
	 */
	public function __construct($response, $tpl_default = NULL ,$tpl_path = NULL) {
		$this->_sw_response = $response;
		$this->_tpl_default = $tpl_default;
		if ($tpl_path === NULL) {
			$tpl_path = Yesf::app()->getConfig('application.dir') . 'views/';
		}
		$this->_tpl_path = $tpl_path;
	}
	/**
	 * 设置模板路径
	 * @param string $tpl_path
	 */
	public function setTplPath($tpl_path) {
		$this->_tpl_path = $tpl_path;
	}
	/**
	 * 关闭模板自动渲染
	 */
	public function disableView() {
		$this->_tpl_auto = FALSE;
	}
	/**
	 * 将一个模板的渲染结果输出至浏览器
	 * @param string $tpl 模板路径
	 */
	public function display($tpl) {
		$this->write($this->render($tpl));
	}
	/**
	 * 获取一个模板的渲染结果但不输出
	 * @param string $tpl 模板路径
	 * @return string
	 */
	public function render($tpl) {
		extract($this->_tpl_vars, EXTR_SKIP);
		ob_start();
		if (is_file($this->_tpl_path . $tpl . '.' . self::$_tpl_extension)) {
			include($this->_tpl_path . $tpl . '.' . self::$_tpl_extension);
		}
		return ob_get_clean();
	}
	/**
	 * 将一个字符串输出至浏览器
	 * @param string $content 要输出的字符串
	 */
	public function write($content) {
		$this->_sw_response->write($content);
	}
	/**
	 * 注册一个模板变量
	 * @param string $k 变量名称
	 * @param mixed $v 值
	 */
	public function assign($k, $v) {
		$this->_tpl_vars[$k] = $v;
	}
	/**
	 * 向浏览器发送一个header信息
	 * @param string $content
	 */
	public function header($content) {
		list($k, $v) = explode(': ', $content, 2);
		$this->_sw_response->header($k, $v);
	}
	/**
	 * 向浏览器发送一个状态码
	 * @param int $code
	 */
	public function status($code) {
		$this->_sw_response->status($code);
	}
	/**
	 * 设置Cookie
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
			$expire = time() +Yesf::app()->getConfig('cookie.expire');
		} elseif ($param['expire'] === -1) {
			$expire = time() - 3600;
		} elseif ($param['expire'] === 0) {
			$expire = 0;
		} else {
			$expire = time() + $param['expire'];
		}
		//其他参数的处理
		!isset($param['path']) && $param['path'] = Yesf::app()->getConfig('cookie.path');
		!isset($param['domain']) && $param['domain'] = Yesf::app()->getConfig('cookie.domain');
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
	 * @param string $extension 扩展名，例如JSON
	 */
	public function mimeType($extension) {
		$this->header(HttpVars::mimeType($extension));
	}
	/**
	 * 析构函数
	 */
	public function __destruct() {
		try {
			if (($this->_tpl_auto === NULL && self::$_tpl_auto_config) || $this->_tpl_auto) {
				$this->display($this->_tpl_default);
			}
			if ($this->_sw_response !== NULL) {
				$this->_sw_response->end();
				$this->_sw_response = NULL;
			}
			$this->_tpl_vars = NULL;
		} catch (\Exception $e) {
			//容错处理
		}
	}
}
