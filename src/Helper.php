<?php
/**
 * 助手类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
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

class Helper {
	public static function setRDAlias() {
		$default = Yesf::app()->getConfig('database.default');
		Container::getInstance()->set(RDInterface::class, function() use ($default) {
			return Pool::getAdapter($default);
		});
	}
	public static function setSessionAlias() {
		$default = Yesf::app()->getConfig('session.handler');
		$clazz = 'Yesf\\Http\\SessionHandler\\' . ucfirst($default);
		Container::getInstance()->set(SessionHandlerInterface::class, $clazz);
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