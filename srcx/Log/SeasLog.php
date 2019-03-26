<?php
/**
 * 基于SeasLog的日志封装类
 * 如果不存在SeasLog，则不会有任何效果，程序照常运行
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Log;
use \yesf\Yesf;
use \Psr\Log\LoggerInterface;

class SeasLog implements LoggerInterface {
	protected $log_level = NULL;
	/**
	 * Init
	 */
	public function __construct() {
		if (!class_exists('\\SeasLog')) {
			return;
		}
		if (Yesf::app()->getConfig('logger.path')) {
			\SeasLog::setBasePath(Yesf::app()->getConfig('logger.path'));
		}
	}
	/**
	 * 记录日志主函数
	 * @access public
	 * @param string $type 日志类型
	 * @param string $message 日志内容
	 */
	public function log(string $type, string $message, array $context = []) {
		//判断是否应该记录
		if (!Logger::should($type)) {
			return;
		}
		//获取SeasLog的常量
		$type = constant('SEASLOG_' . strtoupper($type));
		\SeasLog::log($type, $message, $context, Logger::getLogName());
	}
	/**
	 * 以下为各个级别的封装
	 * 方便调用
	 * @access public
	 * @param string $message
	 */
	public function debug($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function info($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function notice($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function warning($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function error($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function critical($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function alert($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
	public function emergency($message, array $context = []) {
		$this->log(__FUNCTION__, $message, $context);
	}
}