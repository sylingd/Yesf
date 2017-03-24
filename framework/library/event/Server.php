<?php
/**
 * 服务器事件回调
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\event;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\Plugin;

class Server {
	public static $_listener = [];
	/**
	 * 普通事件：启动Master进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventStart($serv) {
		swoole_set_process_name(Yesf::app()->getConfig('application.name') . ' master');
		$pidPath = Yesf::app()->getConfig('swoole.pid')  . '/';
		file_put_contents($pidPath . Yesf::app()->getConfig('application.name') . '_master.pid', $serv->master_pid);
		file_put_contents($pidPath . Yesf::app()->getConfig('application.name') . '_manager.pid', $serv->manager_pid);
	}
	/**
	 * 普通事件：启动Manager进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventManagerStart($serv) {
		swoole_set_process_name(Yesf::app()->getConfig('application.name') . ' manager');
	}
	/**
	 * 普通事件：启动一个进程
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 */
	public static function eventWorkerStart($serv, $worker_id) {
		//根据类型，设置不同的进程名
		if ($serv->taskworker) {
			swoole_set_process_name(Yesf::app()->getConfig('application.name') . ' task ' . $worker_id . ' (Yesf)');
		} else {
			swoole_set_process_name(Yesf::app()->getConfig('application.name') . ' worker ' . $worker_id . ' (Yesf)');
		}
		//回调
		Plugin::trigger('workerStart', [$serv->taskworker, $worker_id]);
	}
	/**
	 * 普通事件：进程出错
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 * @param int $worker_pid
	 * @param int $exit_code
	 */
	public static function eventWorkerError($serv, $worker_id, $worker_pid, $exit_code) {
	}
	/**
	 * 普通事件：接收到task
	 * @access public
	 * @param object $serv
	 * @param int $task_id
	 * @param int $worker_id
	 * @param mixed $data
	 */
	public static function eventTask($serv, $task_id, $worker_id, $data) {
		$rs = Plugin::trigger('taskStart', [$task_id, $worker_id, $data]);
		if (is_string($rs)) {
			return $rs;
		}
	}
	public static function eventFinish($serv, int $task_id, string $data) {
		Plugin::trigger('taskEnd', [$task_id, $data]);
	}
	/**
	 * 进程之间的消息推送
	 * @param object $serv
	 * @param int $from
	 * @param string $message
	 */
	public static function eventPipeMessage($serv, $from, $message) {
		Plugin::trigger('pipeMessage', [$from, $message]);
	}
	/**
	 * TCP事件
	 * 注意：dispatch_mode=1/3时，底层会屏蔽onConnect/onClose事件
	 */
	public static function eventConnect($server, int $fd, int $from_id) {
		$info = $server->connection_info($fd);
		$port = $info['server_port'];
		if (isset(self::$_listener[$port])) {
			self::callback(self::$_listener[$port], 'connect', $fd, $from_id);
		}
	}
	public static function eventClose($server, int $fd, int $from_id) {
		$info = $server->connection_info($fd);
		$port = $info['server_port'];
		if (isset(self::$_listener[$port])) {
			self::callback(self::$_listener[$port], 'close', $fd, $from_id);
		}
	}
	public static function eventReceive($server, int $fd, int $from_id, string $data) {
		$info = $server->connection_info($fd, $from_id);
		$port = $info['server_port'];
		if (isset(self::$_listener[$port])) {
			self::callback(self::$_listener[$port], 'receive', $fd, $from_id, $data);
		}
	}
	/**
	 * UDP事件
	 */
	public static function eventPacket($server, string $data, array $client_info) {
		$fd = unpack('L', pack('N', ip2long($client_info['address'])))[1];
		$from_id = ($client_info['server_socket'] << 16) + $client_info['port'];
		$port = $client_info['server_port'];
		if (isset(self::$_listener[$port])) {
			self::callback(self::$_listener[$port], 'receive', $fd, $from_id, $data);
		}
	}
	/**
	 * 模拟call_user_func
	 */
	protected static function callback($call, $event, $fd, $from_id, $data = NULL) {
		if (is_array($call)) {
			list($class, $method) = $call;
			if (is_object($class)) {
				return $class->$method($event, $fd, $from_id, $data);
			} else {
				return $class::$method($event, $fd, $from_id, $data);
			}
		} else {
			return $call($event, $fd, $from_id, $data);
		}
	}
}