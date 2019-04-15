<?php
/**
 * 助手类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2019 ShuangYa
 * @license https://yesf.sylibs.com/license.html
 */
namespace Yesf;

use SessionHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Yesf\Cache\File;
use Yesf\DI\Container;
use Yesf\RD\RDInterface;
use Yesf\Connection\Pool;
use Yesf\Http\Router;
use Yesf\Http\RouterInterface;
use Yesf\Http\SessionHandler;

class Helper {
	public static function setRDAlias() {
		$default = Yesf::app()->getConfig('database.default');
		Container::getInstance()->set(RDInterface::class, function() use ($default) {
			return Pool::getAdapter($default);
		});
	}
	public static function setSessionAlias() {
		Container::getInstance()->set(SessionHandlerInterface::class, SessionHandler::class);
	}
	public static function setCacheAlias() {
		$default = Yesf::app()->getConfig('cache.default');
		if ($default === 'file') {
			Container::getInstance()->set(CacheInterface::class, File::class);
		} else {
			Container::getInstance()->set(CacheInterface::class, function() use ($default) {
				return Pool::getAdapter($default);
			});
		}
	}
	public static function setRouterAlias() {
		Container::getInstance()->set(RouterInterface::class, Router::class);
	}
}