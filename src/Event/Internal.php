<?php
/**
 * Yesf自用事件
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
use Yesf\Logger;
use Yesf\Http\Dispatcher;
use Yesf\Http\Response;
use Yesf\Connection\Pool;

class Internal {
	public static function onWorkerStart() {
		Yesf::app()->loadEnvConfig();
		Yesf::app()->loadProjectConfig();
		Logger::init();
		Dispatcher::init();
		Response::init();
		Response::initInWorker();
		Pool::init();
	}
}