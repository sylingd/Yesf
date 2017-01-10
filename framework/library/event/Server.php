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

class Server {
	public static $eventHandle = [];
	/**
	 * 添加事件响应函数
	 * @access public
	 * @param string $event
	 * @param int $port 当事件为WorkerStart时传入0
	 * @param callable $callback
	 */
	public static function addEventHandle(string $event, int $port, callable $callback) {
		if (!isset(self::$eventHandle[$port])) {
			self::$eventHandle[$port] = [];
		}
		self::$eventHandle[$port][$event] = $callback;
	}
	public static function triggerEventHandle(string $event, int $port, array $param) {
		if (isset(self::$eventHandle[$port][$event]) && is_callable(self::$eventHandle[$port][$event])) {
			call_user_func_array(Server::$eventHandle[$port][$event], $param);
		}
	}
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
		self::triggerEventHandle('WorkerStart', 0, [$serv->taskworker, $worker_id]);
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
	 * @param int $worker_id
	 * @param int $worker_pid
	 * @param int $exit_code
	 */
	public static function eventTask($serv, $task_id, $from_id, $task) {
		//TODO
	}
	public static function eventFinish($serv, int $task_id, string $data) {
		
	}
}