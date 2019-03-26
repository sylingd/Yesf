<?php
/**
 * HTTP其他参数
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace Yesf\Http;
use Yesf\Yesf;

class Vars {
	protected static $mimeTypes = NULL;
	public static function mimeType($extension, $includeCharset = TRUE) {
		if (self::$mimeTypes === NULL) {
			self::$mimeTypes = require(YESF_ROOT . 'Data/mimeTypes.php');
		}
		$extension = strtolower($extension);
		if (!isset(self::$mimeTypes[$extension])) {
			return 'application/octet-stream';
		}
		$mimeType = self::$mimeTypes[$extension];
		if (in_array($extension, ['js', 'json', 'atom', 'rss', 'xhtml'], TRUE) || substr($mimeType, 0, 5) === 'text/') {
			$mimeType .= '; charset=' . Yesf::getProjectConfig('charset');
		}
		return $mimeType;
	}
}