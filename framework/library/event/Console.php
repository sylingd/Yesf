<?php
/**
 * 控制台事件回调
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\event;
use \yesf\Yesf;
use \yesf\Constant;
use \yesf\library\Swoole;

class Console {
	public static function receive($type, $fd, $from_id, $data = NULL) {
		if ($type !== 'receive') {
			return;
		}
		$data = json_decode(substr($data, 4), 1); //为了降低环境需求，这里使用JSON
		$action = $data['action'] . 'Action';
		if (method_exists(__CLASS__, $action)) {
			self::$action($fd, $from_id, $data);
		}
	}
	protected static function send($fd, $from_id, $data) {
		$sendStr = json_encode($data);
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		Swoole::send($sendData, $fd, $from_id);
	}
	public static function getStatAction($fd, $from_id, $data) {
		$stat = Swoole::getStat();
		self::send($fd, $from_id, $stat);
	}
	public static function reloadAction($fd, $from_id, $data) {
		$stat = Swoole::getStat();
		self::send($fd, $from_id, $stat);
		Swoole::reload($data['task'] == 1);
	}
}