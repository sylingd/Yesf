<?php
/**
 * 常量类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
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
	const LISTEN_TCP = 1;
	const LISTEN_UDP = 2;
	const LISTEN_UNIX = 3;
}