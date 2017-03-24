<?php
/**
 * Swoole主要操作类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
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
	protected static $server = NULL;
	/**
	 * 初始化
	 */
	public static function init() {
		self::$server = new \swoole_http_server(Yesf::app()->getConfig('swoole.ip'), Yesf::app()->getConfig('swoole.port')); 
		//基本配置
		$config = Yesf::app()->getConfig('swoole.advanced');
		if (is_object($config)) {
			if (method_exists($config, 'toArray')) {
				$config = $config->toArray();
			} else {
				$config = (array)$config;
			}
		}
		$ssl = Yesf::app()->getConfig('swoole.ssl');
		if ($ssl['enable']) {
			$config['ssl_cert_file'] = $ssl['cert'];
			$config['ssl_key_file'] = $ssl['key'];
		}
		if (Yesf::app()->getConfig('swoole.http2')) {
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
		self::$server->on('PipeMessage', ['\yesf\library\event\Server', 'eventPipeMessage']);
		//HTTP事件
		self::$server->on('Request', ['\yesf\library\event\HttpServer', 'eventRequest']);
		self::$server->on('Task', ['\yesf\library\event\Server', 'eventTask']);
	}
	public static function start() {
		self::$server->start();
	}
	public static function initConsole() {
		$ip =  Yesf::app()->getConfig('swoole.console.ip');
		$port =  Yesf::app()->getConfig('swoole.console.port');
		if (empty($ip) || empty($port)) {
			return;
		}
		$config = [
			'ip' => $ip,
			'port' => $port, //监听端口
			'advanced' => [ //关于Swoole的高级选项
				'open_length_check' => 1,
				'package_length_type' => 'N',
				'package_length_offset' => 0,
				'package_body_offset' => 4,
				'package_max_length' => 1048576, // 1024 * 1024,
				'open_tcp_nodelay' => 1,
				'backlog' => 100,
			]
		];
		self::addListener(Constant::LISTEN_TCP, $config, '\\yesf\\library\\event\\Console::receive');
	}
	/**
	 * 获取统计数据
	 * @return array
	 */
	public static function getStat() {
		return self::$server->stats();
	}
	/**
	 * 重载
	 *@param boolean $task
	 */
	public static function reload($task = FALSE) {
		self::$server->reload($task);
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
			if (is_object($config)) {
				if (method_exists($config, 'toArray')) {
					$config = $config->toArray();
				} else {
					$config = (array)$config;
				}
			}
		}
		if (!isset($config['port'])) {
			return FALSE;
		}
		$port = $config['port'];
		if (isset(Server::$_listener[$port])) {
			return FALSE;
		}
		$ip = isset($config['ip']) ? $config['ip'] : Yesf::app()->getConfig('swoole.ip');
		Server::$_listener[$port] = $callback;
		if ($type === Constant::LISTEN_TCP) {
			$service = self::$server->addListener($ip, $port, \SWOOLE_TCP);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$service->on('Receive', ['\yesf\library\event\Server', 'eventReceive']);
			$service->on('Connect', ['\yesf\library\event\Server', 'eventConnect']);
			$service->on('Close', ['\yesf\library\event\Server', 'eventClose']);
		} elseif ($type === Constant::LISTEN_UDP) {
			$service = self::$server->addListener($ip, $port, \SWOOLE_UDP);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$service->on('Packet', ['\yesf\library\event\Server', 'eventPacket']);
		}
		return TRUE;
	}
	/**
	 * 向客户端发送消息
	 * @param string $data
	 * @param int $fd
	 * @param int $from_id
	 */
	public static function send(string $data, int $fd, int $from_id = 0) {
		self::$server->send($fd, $data, $from_id);
	}
	/**
	 * 投递Task
	 * @param mixed $data 传递数据
	 * @param int $worker_id 投递到的task进程ID
	 * @param callable $callback 回调函数
	 */
	public static function task($data, $worker_id = -1, $callback = NULL) {
		if ($callback === NULL) {
			self::$server->task($data, $worker_id);
		} else {
			self::$server->task($data, $worker_id, $callback);
		}
	}
	/**
	 * 发送消息到某个worker进程（支持task_worker）
	 * @param string $message
	 * @param int $worker_id
	 */
	public static function sendToWorker($message, $worker_id) {
		self::$server->sendMessage($message, $worker_id);
	}
}