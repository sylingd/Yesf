<?php
/**
 * 部分特殊内容转换
 * 
 * @author ShuangYa
 * @package Yesf
 * @category DI
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\DI;

use Psr\Container\ContainerInterface;
use Yesf\Exception\NotFoundException;
use Yesf\Exception\InvalidClassException;
use Yesf\Exception\CyclicDependencyException;

class GetEntryUtil {
	public static function controller($name) {
		$controllerName = Yesf::getAppNamespace() . '\\Modules\\' . $module . '\\Controller\\' . ucfirst($controller);
		return $controllerName;
	}
}