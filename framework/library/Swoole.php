<?php
/**
 * Swoole主要操作类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\event\Server;

class Swoole {
	//Swoole实例类
	protected $server = NULL;
	/**
	 * 初始化
	 */
	public static function init() {
		self::$server = new \swoole_http_server($config->get('swoole.ip'), $config->get('swoole.port')); 
		//基本配置
		$config = Yesf::app()->getConfig('swoole.http.advanced');
		$ssl = Yesf::app()->getConfig('swoole.http.ssl');
		if ($ssl['enable']) {
			$config['ssl_cert_file'] = $ssl['cert'];
			$config['ssl_key_file'] = $ssl['key'];
		}
		if (Yesf::app()->getConfig('swoole.http.http2')) {
			if (!isset($config['ssl_cert_file'])) {
				throw new \yesf\library\exception\StartException('Certfile not found');
			}
			$config['open_http2_protocol'] = TRUE;
		}
		self::$server->set($config);
		//基本事件
		self::$server->on('Start', ['\yesf\library\event\Server', 'eventStart']);
		self::$server->on('ManagerStart', ['\yesf\library\event\Server', 'eventManagerStart']);
		self::$server->on('WorkerStart', ['\yesf\library\event\Server', 'eventWorkerStart']);
		self::$server->on('WorkerError', ['\yesf\library\event\Server', 'eventWorkerError']);
		self::$server->on('Finish', ['\yesf\library\event\Server', 'eventFinish']);
		//HTTP事件
		self::$server->on('Request', ['\yesf\library\event\HttpServer', 'eventRequest']);
		self::$server->on('Task', ['\yesf\library\event\Server', 'eventTask']);
	}
	public static function start() {
		self::$server->start();
	}
	/**
	 * 添加监听
	 * @param int $type 监听类型
	 * @param mixed $config 选项，可以为数组或配置项名称
	 * @param callable $callback 回调函数
	 * @return boolean
	 */
	public static function addListener(int $type, $config, callable $callback) {
		if (is_string($config)) {
			$config = Yesf::app()->getConfig($config);
		}
		if (!isset($config['port'])) {
			return FALSE
		}
		if (isset(Server::$_listener[$port])) {
			return FALSE;
		}
		Server::$_listener[$port] = $callback;
		$ip = isset($config['ip']) ? $config['ip'] : Yesf::app()->getConfig('swoole.ip');
		$port = $config['port'];
		if ($type === Constant::LISTEN_TCP) {
			$service = self::$server->listen($ip, $port, \SWOOLE_TCP);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$service->on('Receive', ['\yesf\library\event\Server', 'eventReceive']);
			$service->on('Connect', ['\yesf\library\event\Server', 'eventConnect']);
			$service->on('Close', ['\yesf\library\event\Server', 'eventClose']);
		} elseif ($type === Constant::LISTEN_UDP) {
			$service = self::$server->listen($ip, $port, \SWOOLE_UDP);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$service->on('Receive', ['\yesf\library\event\Server', 'eventReceive']);
			$service->on('Packet', ['\yesf\library\event\Server', 'eventPacket']);
		}
		return TRUE;
	}
}