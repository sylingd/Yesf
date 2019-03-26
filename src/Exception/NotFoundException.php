<?php
/**
 * 启动异常类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Exception
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface {
}