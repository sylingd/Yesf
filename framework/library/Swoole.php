<?php
/**
 * Swoole主要操作类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;
use \Swoole\Http\Server as SwServer;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\event\Server;
use \yesf\library\exception\StartException;

class Swoole {
	//当前是否为task进程，在workerStart后才有效
	public static $isTaskWorker = FALSE;
	//Swoole实例类
	protected static $server = NULL;
	/**
	 * 初始化
	 */
	public static function init() {
		self::$server = new SwServer(Yesf::app()->getConfig('swoole.ip'), Yesf::app()->getConfig('swoole.port')); 
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
				throw new StartException('Certfile not found');
			}
			$config['open_http2_protocol'] = TRUE;
		}
		self::$server->set($config);
		//基本事件
		self::$server->on('Start', __NAMESPACE__ . '\\Server::eventStart');
		self::$server->on('ManagerStart', __NAMESPACE__ . '\\Server::eventManagerStart');
		self::$server->on('WorkerStart', __NAMESPACE__ . '\\Server::eventWorkerStart');
		self::$server->on('WorkerError', __NAMESPACE__ . '\\Server::eventWorkerError');
		self::$server->on('Finish', __NAMESPACE__ . '\\Server::eventFinish');
		self::$server->on('PipeMessage', __NAMESPACE__ . '\\Server::eventPipeMessage');
		//HTTP事件
		self::$server->on('Request', __NAMESPACE__ . '\\Server::eventRequest');
		self::$server->on('Task', __NAMESPACE__ . '\\Server::eventTask');
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
		//If type is unix, do not need port
		if ($type === Constant::LISTEN_UNIX || $type === Constant::LISTEN_UNIX_DGRAM) {
			$addr = $config['sock'];
			$port = 0;
			if (empty($addr)) {
				return FALSE;
			}
			if (isset(Server::$_listener[$addr])) {
				return FALSE;
			}
			Server::$_listener[$addr] = $callback;
		} else {
			$addr = isset($config['ip']) ? $config['ip'] : Yesf::app()->getConfig('swoole.ip');
			if (!isset($config['port'])) {
				return FALSE;
			}
			$port = $config['port'];
			if (isset(Server::$_listener[$port])) {
				return FALSE;
			}
			Server::$_listener[$port] = $callback;
		}
		if ($type === Constant::LISTEN_TCP || $type === Constant::LISTEN_TCP6 || $type === Constant::LISTEN_UNIX) {
			$service = self::$server->addListener($addr, $port, $type);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$callback_key = ($type === Constant::LISTEN_UNIX ? $addr : $port);
			$service->on('Receive', function($server, $fd, $from_id, $data) use ($callback_key) {
				Server::eventReceive($callback_key, $fd, $from_id, $data);
			});
			$service->on('Connect', function($server, $fd, $from_id) use ($callback_key) {
				Server::eventConnect($callback_key, $fd, $from_id);
			});
			$service->on('Close', function($server, $fd, $from_id) use ($callback_key) {
				Server::eventClose($callback_key, $fd, $from_id);
			});
		} elseif ($type === Constant::LISTEN_UDP || $type === Constant::LISTEN_UDP6 || $type === Constant::LISTEN_UNIX_DGRAM) {
			$service = self::$server->addListener($addr, $port, $type);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$callback_key = ($type === Constant::LISTEN_UNIX_DGRAM ? $addr : $port);
			$service->on('Packet', function($server, string $data, array $client_info) use ($callback_key) {
				Server::eventPacket($callback_key, $data, $client_info);
			});
		}
		return TRUE;
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
	 * 向客户端发送消息
	 * @param string $data
	 * @param int $fd
	 * @param int $from_id
	 */
	public static function send(string $data, $fd, $from_id = 0) {
		self::$server->send($fd, $data, $from_id);
	}
	/**
	 * 向UDP客户端发送消息
	 * @param string $data
	 * @param mixed $addr
	 * @param int $port
	 */
	public static function sendToUDP(string $data, $addr, $port = 0, $from = -1) {
		self::$server->sendto($addr, $port, $data);
	}
	/**
	 * 发送消息到某个worker进程（支持task_worker）
	 * @param string $message
	 * @param int $worker_id
	 */
	public static function sendToWorker($message, $worker_id) {
		self::$server->sendMessage($message, $worker_id);
	}
	/**
	 * 获取Swoole示例，用于实现更多高级操作
	 * @return \object(\Swoole\Server)
	 */
	public static function getSwoole() {
		return self::$server;
	}
	/**
	 * 兼容性补丁
	 * 用于兼容call_user_func和call_user_func_array
	 * 在某些情况下可能导致协程无法恢复上下文
	 * 导致请求卡住
	 */
	public static function call_user_func($func, ...$args) {
		return self::call_user_func_array($func, $args);
	}
	public static function call_user_func_array($func, $args) {
		if (is_callable('\Swoole\Coroutine::call_user_func_array')) {
			return \Swoole\Coroutine::call_user_func_array($func, $args); // @codeCoverageIgnore
		} else {
			return $func(...$args);
		}
	}
}