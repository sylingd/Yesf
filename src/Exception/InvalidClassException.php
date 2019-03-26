<?php
/**
 * 尝试创建无效的类
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

class InvalidClassException extends Exception implements ContainerExceptionInterface {
}