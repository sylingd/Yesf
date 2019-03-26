<?php
/**
 * 服务器事件回调
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace Yesf\Event;
use Yesf\Yesf;
use Yesf\Constant;
use Yesf\Swoole;
use Yesf\Plugin;

class Server {
	public static $_listener = [];
	private static $_hot_reload_lock = NULL;
	private static function setProcessName($name) {
		if (function_exists('cli_set_process_title')) {
			cli_set_process_title($name);
		} else {
			swoole_set_process_name($name);
		}
	}
	/**
	 * 普通事件：启动Master进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventStart($serv) {
		self::setProcessName(Yesf::getProjectConfig('name') . ' master');
		$pidPath = rtrim(Yesf::getServerConfig('pid'), '/') . '/';
		try {
			file_put_contents($pidPath . Yesf::getProjectConfig('name') . '_master.pid', $serv->master_pid);
			file_put_contents($pidPath . Yesf::getProjectConfig('name') . '_manager.pid', $serv->manager_pid);
		} catch (\Exception $e) {
			//忽略写入错误
		}
	}
	/**
	 * 普通事件：关闭程序
	 * @access public
	 * @param object $serv
	 */
	public static function eventShutdown($serv) {
		$pidPath = rtrim(Yesf::getServerConfig('pid'), '/') . '/';
		@unlink($pidPath . Yesf::getProjectConfig('name') . '_master.pid');
		@unlink($pidPath . Yesf::getProjectConfig('name') . '_manager.pid');
	}
	/**
	 * 普通事件：启动Manager进程
	 * @access public
	 * @param object $serv
	 */
	public static function eventManagerStart($serv) {
		self::setProcessName(Yesf::getProjectConfig('name') . ' manager');
	}
	public static function eventManagerStop() {
	}
	/**
	 * 启动热更新功能
	 * 
	 * @access protected
	 */
	public static function prepareHotReload() {
		if (Yesf::app()->getEnvironment() === 'develop' && function_exists('inotify_init')) {
			self::$_hot_reload_lock = new \Swoole\Lock(SWOOLE_MUTEX);
		}
	}
	protected static function initHotReload($serv) {
		//判断是否启动热更新功能
		if (self::$_hot_reload_lock === NULL || !self::$_hot_reload_lock->trylock()) {
			return;
		}
		$pid = $serv->master_pid;
		$watcher_name = Yesf::getProjectConfig('name') . ' hot reload';
		$watcher_process = new \Swoole\Process(function($worker) use ($watcher_name, &$pid, &$worker_pid) {
			if (function_exists('cli_set_process_title')) {
				cli_set_process_title($watcher_name);
			} else {
				swoole_set_process_name($watcher_name);
			}
			$notify = inotify_init();
			//因为监听目录后，目录下的文件操作也会触发，所以只获取目录
			$list = [];
			$scan_dir = function($dir) use (&$scan_dir, &$list, &$notify) {
				if (is_dir($dir)) {
					$list[inotify_add_watch($notify, $dir, IN_ALL_EVENTS)] = $dir;
					$files = array_diff(scandir($dir), ['.', '..']);
					foreach ($files as $file) {
						$scan_dir($dir . '/' . $file);
					}
				}
			};
			$scan_dir(rtrim(APP_PATH, '/'));
			//加入EventLoop
			$reload_timer = NULL;
			swoole_event_add($notify, function() use (&$notify, &$list, &$pid, &$reload_timer) {
				$events = inotify_read($notify);
				if (!empty($events)) {
					$require_reload = FALSE;
					foreach ($events as $event) {
						$mask = $event['mask'];
						if ($mask & IN_ISDIR) {
							$mask = $mask ^ IN_ISDIR;
						}
						$fullpath = $list[$event['wd']] . '/' . $event['name'];
						switch ($mask) {
							case IN_CREATE:
							case IN_MOVED_TO:
								$require_reload = TRUE;
								//添加目录时，建立监听
								if (is_dir($fullpath)) {
									$list[inotify_add_watch($notify, $fullpath, IN_ALL_EVENTS)] = $fullpath;
								}
								break;
							case IN_DELETE_SELF:
								$require_reload = TRUE;
								//自身被删除
								unset($list[$event['wd']]);
								break;
							case IN_DELETE:
							case IN_MOVED_FROM:
								$require_reload = TRUE;
								if (($key = array_search($fullpath, $list, TRUE)) !== FALSE) {
									unset($list[$key]);
								}
								break;
							case IN_MODIFY:
								$require_reload = TRUE;
								break;
						}
					}
					if ($require_reload) {
						//延时0.5s
						if ($reload_timer !== NULL) {
							swoole_timer_clear($reload_timer);
							$reload_timer = NULL;
						}
						$reload_timer = swoole_timer_after(500, function() use (&$pid, &$reload_timer) {
							$reload_timer = NULL;
							\Swoole\Process::kill($pid, SIGUSR1);
						});
					}
				}
			});
			//检查master进程是否存在
			swoole_timer_tick(1000, function() use (&$pid, &$worker) {
				if (!\Swoole\Process::kill($pid, 0)) {
					$worker->exit();
				}
			});
		}, FALSE);
		$watcher_process->start();
		\Swoole\Process::signal(SIGCHLD, function($sig) {
			//必须为false，非阻塞模式
			while ($ret = \Swoole\Process::wait(false)) {
			}
		});
	}
	/**
	 * 普通事件：启动一个进程
	 * @access public
	 * @param object $serv
	 * @param int $worker_id
	 */
	public static function eventWorkerStart($serv, $worker_id) {
		Yesf::app()->initInWorker();
		//根据类型，设置不同的进程名
		if ($serv->taskworker) {
			self::setProcessName(Yesf::getProjectConfig('name') . ' task ' . $worker_id);
		} else {
			self::initHotReload($serv);
			self::setProcessName(Yesf::getProjectConfig('name') . ' worker ' . $worker_id);
		}
		//清除opcache
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}
		//标记一下
		Swoole::$isTaskWorker = $serv->taskworker;
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
	public static function eventConnect($callback_key, $fd, $from_id) {
		if (isset(self::$_listener[$callback_key])) {
			call_user_func(self::$_listener[$callback_key], 'connect', $fd, $from_id);
		}
	}
	public static function eventClose($callback_key, $fd, $from_id) {
		if (isset(self::$_listener[$callback_key])) {
			call_user_func(self::$_listener[$callback_key], 'close', $fd, $from_id);
		}
	}
	public static function eventReceive($callback_key, $fd, $from_id, string $data) {
		if (isset(self::$_listener[$callback_key])) {
			call_user_func(self::$_listener[$callback_key], 'receive', $fd, $from_id, $data);
		}
	}
	/**
	 * UDP事件
	 */
	public static function eventPacket($callback_key, string $data, array $client_info) {
		if (is_numeric($callback_key)) {
			$fd = unpack('L', pack('N', ip2long($client_info['address'])))[1];
			$from_id = ($client_info['server_socket'] << 16) + $client_info['port'];
		} else {
			$fd = $client_info['address'];
			$from_id = $callback_key;
		}
		if (isset(self::$_listener[$callback_key])) {
			call_user_func(self::$_listener[$callback_key], 'receive', $fd, $from_id, $data);
		}
	}
}