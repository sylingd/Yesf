<?php
/**
 * 常量类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf;

class Constant {
	const ROUTER_VALID = 0;
	const ROUTER_ERR_MODULE = 1;
	const ROUTER_ERR_CONTROLLER = 2;
	const ROUTER_ERR_ACTION = 3;
	const CONFIG_YACONF = 1;
	const CONFIG_QCONF = 2;
	const CONFIG_YAF = 3;
	const CONFIG_FILE = 4;
	const LISTEN_TCP = SWOOLE_TCP;
	const LISTEN_UDP = SWOOLE_UDP;
	const LISTEN_UNIX = SWOOLE_UNIX_STREAM;
	const LISTEN_UNIX_DGRAM = SWOOLE_UNIX_DGRAM;
	const LISTEN_TCP6 = SWOOLE_TCP6;
	const LISTEN_UDP6 = SWOOLE_UDP6;
	const TYPE_AUTO = 0;
	const TYPE_CORO = 1;
	const TYPE_SYNC = 2;
}