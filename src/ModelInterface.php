<?php
/**
 * Model接口
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace Yesf;

use Yesf\RD\RDInterface;
use Yesf\Database\Database;
use Yesf\Exception\Exception;
use Yesf\Exception\DBException;

interface ModelInterface {
	public function __construct(RDInterface $driver);
}