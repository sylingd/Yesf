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
use Yesf\Helper;
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
		Helper::setRDAlias();
		Helper::setSessionAlias();
		Helper::setCacheAlias();
		Pool::init();
	}
	/**
	 * 内部事件
	 * 
	 * @access public
	 */
	public static function onCreate() {
		Helper::setRouterAlias();
	}
}