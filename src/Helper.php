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

use Psr\SimpleCache\CacheInterface;
use Yesf\Cache\File;
use Yesf\DI\Container;
use Yesf\RD\RDInterface;
use Yesf\Connection\Pool;

class Helper {
	public static function setRDAlias() {
		$default = Yesf::app()->getConfig('database');
		Container::getInstance()->set(RDInterface::class, function() use ($default) {
			return Pool::getAdapter($default);
		});
	}
	public static function setCacheAlias() {
		$default = Yesf::app()->getConfig('cache');
		if ($default === 'file') {
			Container::getInstance()->set(CacheInterface::class, File::class);
		} else {
			Container::getInstance()->set(CacheInterface::class, function() use ($default) {
				return Pool::getAdapter($default);
			});
		}
	}
}