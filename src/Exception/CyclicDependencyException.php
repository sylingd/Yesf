<?php
/**
 * 循环依赖
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Exception
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Exception;

use Psr\Container\ContainerExceptionInterface;

class CyclicDependencyException extends Exception implements ContainerExceptionInterface {
}