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

namespace Yesf;
use Swoole\Http\Server as SwServer;
use Yesf\Yesf;
use Yesf\Event\Server;
use Yesf\Exception\NotFoundException;

class Swoole {
	const LISTEN_TCP = SWOOLE_TCP;
	const LISTEN_UDP = SWOOLE_UDP;
	const LISTEN_UNIX = SWOOLE_UNIX_STREAM;
	const LISTEN_UNIX_DGRAM = SWOOLE_UNIX_DGRAM;
	const LISTEN_TCP6 = SWOOLE_TCP6;
	const LISTEN_UDP6 = SWOOLE_UDP6;
	//当前是否为task进程，在workerStart后才有效
	public static $isTaskWorker = FALSE;
	//Swoole实例类
	protected static $server = NULL;
	/**
	 * 初始化
	 * 
	 * @access public
	 */
	public static function init() {
		self::$server = new SwServer(Yesf::getServerConfig('ip'), Yesf::getServerConfig('port')); 
		//基本配置
		$config = Yesf::getServerConfig('advanced');
		if (is_object($config)) {
			if (method_exists($config, 'toArray')) {
				$config = $config->toArray();
			} else {
				$config = (array)$config;
			}
		}
		$ssl = Yesf::getServerConfig('ssl');
		if ($ssl && $ssl['enable']) {
			$config['ssl_cert_file'] = $ssl['cert'];
			$config['ssl_key_file'] = $ssl['key'];
		}
		if (Yesf::getServerConfig('http2')) {
			if (!isset($config['ssl_cert_file'])) {
				throw new NotFoundException('Certfile not found');
			}
			$config['open_http2_protocol'] = TRUE;
		}
		self::$server->set($config);
		//是否启用热更新
		Server::prepareHotReload();
		//基本事件
		self::$server->on('Start', __NAMESPACE__ . '\\event\\Server::eventStart');
		self::$server->on('Shutdown', __NAMESPACE__ . '\\event\\Server::eventShutdown');
		self::$server->on('ManagerStart', __NAMESPACE__ . '\\event\\Server::eventManagerStart');
		self::$server->on('ManagerStop', __NAMESPACE__ . '\\event\\Server::eventManagerStop');
		self::$server->on('WorkerStart', __NAMESPACE__ . '\\event\\Server::eventWorkerStart');
		self::$server->on('WorkerError', __NAMESPACE__ . '\\event\\Server::eventWorkerError');
		self::$server->on('Finish', __NAMESPACE__ . '\\event\\Server::eventFinish');
		self::$server->on('PipeMessage', __NAMESPACE__ . '\\event\\Server::eventPipeMessage');
		self::$server->on('Task', __NAMESPACE__ . '\\event\\Server::eventTask');
		//HTTP事件
		self::$server->on('Request', __NAMESPACE__ . '\\event\\HttpServer::eventRequest');
	}
	public static function start() {
		self::$server->start();
	}
	/**
	 * 获取统计数据
	 * 
	 * @access public
	 * @return array
	 */
	public static function getStat() {
		return self::$server->stats();
	}
	/**
	 * 重载
	 * 
	 * @access public
	 * @param boolean $task 是否重载Task进程
	 */
	public static function reload($task = TRUE) {
		self::$server->reload($task);
	}
	/**
	 * 添加监听
	 * 
	 * @access public
	 * @param int $type 监听类型
	 * @param mixed $config 选项，可以为数组或配置项名称
	 * @param callable $callback 回调函数
	 * @return boolean
	 */
	public static function addListener(int $type, $config, callable $callback) {
		if (is_string($config)) {
			$config = Yesf::getServerConfig($config);
			if (is_object($config)) {
				if (method_exists($config, 'toArray')) {
					$config = $config->toArray();
				} else {
					$config = (array)$config;
				}
			}
		}
		//If type is unix, do not need port
		if ($type === self::LISTEN_UNIX || $type === self::LISTEN_UNIX_DGRAM) {
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
			$addr = isset($config['ip']) ? $config['ip'] : Yesf::getServerConfig('ip');
			if (!isset($config['port'])) {
				return FALSE;
			}
			$port = $config['port'];
			if (isset(Server::$_listener[$port])) {
				return FALSE;
			}
			Server::$_listener[$port] = $callback;
		}
		if ($type === self::LISTEN_TCP || $type === self::LISTEN_TCP6 || $type === self::LISTEN_UNIX) {
			//Unix或TCP
			$service = self::$server->addListener($addr, $port, $type);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$callback_key = ($type === self::LISTEN_UNIX ? $addr : $port);
			$service->on('Receive', function($server, $fd, $from_id, $data) use ($callback_key) {
				Server::eventReceive($callback_key, $fd, $from_id, $data);
			});
			$service->on('Connect', function($server, $fd, $from_id) use ($callback_key) {
				Server::eventConnect($callback_key, $fd, $from_id);
			});
			$service->on('Close', function($server, $fd, $from_id) use ($callback_key) {
				Server::eventClose($callback_key, $fd, $from_id);
			});
		} elseif ($type === self::LISTEN_UDP || $type === self::LISTEN_UDP6 || $type === self::LISTEN_UNIX_DGRAM) {
			//Unix dgram或UDP
			$service = self::$server->addListener($addr, $port, $type);
			if (isset($config['advanced'])) {
				$service->set($config['advanced']);
			}
			$callback_key = ($type === self::LISTEN_UNIX_DGRAM ? $addr : $port);
			$service->on('Packet', function($server, string $data, array $client_info) use ($callback_key) {
				Server::eventPacket($callback_key, $data, $client_info);
			});
		}
		return TRUE;
	}
	/**
	 * 投递Task
	 * 
	 * @access public
	 * @param mixed $data 传递数据
	 * @param int $worker_id 投递到的task进程ID
	 * @param callable $callback 回调函数
	 */
	public static function task($data, $worker_id = -1, $callback = NULL) {
		if ($callback === TRUE) {
			return self::$server->taskCo([$data]);
		} elseif (is_callable($callback)) {
			self::$server->task($data, $worker_id, $callback);
		} else {
			self::$server->task($data, $worker_id);
		}
	}
	/**
	 * 批量投递Task
	 * 对于不同的$callback，有如下三种处理方式：
	 * $callback为TRUE：使用协程方式等待
	 * $callback为回调函数：使用异步投递，并等待返回
	 * $callback为空：异步投递
	 * 
	 * @access public
	 * @param array $data 传递数据
	 * @param boolean/callable $callback 回调函数
	 */
	public static function taskMulit($data, $callback) {
		if ($callback === TRUE) {
			return self::$server->taskCo($data);
		} elseif (is_callable($callback)) {
			$result = [];
			$ids = [];
			foreach ($data as $k => $v) {
				$task_id = self::$server->task($v, -1, function($serv, $id, $rs) use (&$data, &$result, &$callback) {
					$result[$ids[$id]] = $rs;
					if (count($result) === count($data)) {
						$callback($data);
					}
				});
				$ids[$task_id] = $k;
			}
		} else {
			foreach ($data as $k => $v) {
				self::$server->task($v, -1);
			}
		}
	}
	/**
	 * 向客户端发送消息
	 * 
	 * @access public
	 * @param string $data
	 * @param int $fd
	 * @param int $from_id
	 */
	public static function send(string $data, $fd, $from_id = 0) {
		self::$server->send($fd, $data, $from_id);
	}
	/**
	 * 向UDP客户端发送消息
	 * 
	 * @access public
	 * @param string $data
	 * @param mixed $addr
	 * @param int $port
	 */
	public static function sendToUDP(string $data, $addr, $port = 0, $from = -1) {
		self::$server->sendto($addr, $port, $data);
	}
	/**
	 * 发送消息到某个worker进程（支持task_worker）
	 * 
	 * @access public
	 * @param string $message
	 * @param int $worker_id
	 */
	public static function sendToWorker($message, $worker_id) {
		self::$server->sendMessage($message, $worker_id);
	}
	/**
	 * 获取Swoole示例，用于实现更多高级操作
	 * 
	 * @access public
	 * @return object(\Swoole\Server)
	 */
	public static function getSwoole() {
		return self::$server;
	}
}