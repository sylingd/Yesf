<?php
/**
 * Yesf自用事件
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Swoole
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2019 ShuangYa
 * @license https://yesf.sylibs.com/license.html
 */
namespace Yesf\Event;

use Yesf\Yesf;
use Yesf\Utils;
use Yesf\Log\Logger;
use Yesf\Http\Response;
use Yesf\Connection\Pool;

class Internal {
	/**
	 * 内部事件
	 * 
	 * @access public
	 */
	public static function onWorkerStart() {
		Yesf::app()->loadEnvConfig();
		Yesf::loadProjectConfig();
		Logger::init();
		Response::init();
		Response::initInWorker();
		Utils::setRDAlias();
		Utils::setCacheAlias();
		Pool::init();
	}
	/**
	 * 内部事件
	 * 
	 * @access public
	 */
	public static function onCreate() {
		Utils::setRouterAlias();
		Utils::setSessionAlias();
	}
}