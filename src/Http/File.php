<?php
/**
 * HTTP文件封装
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

class File {
	/** @var array $file File info array */
	private $file;

	public function __construct(&$file) {
		$this->file = $file;
	}

	public function getName() {
		return $this->file['name'];
	}
	
	public function getType() {
		return $this->file['type'];
	}

	public function getPath() {
		return $this->file['tmp_name'];
	}

	public function getSize() {
		return $this->file['size'];
	}

	public function save($path) {
		$path = realpath($path);
		$res = rename($this->getPath(), $path);
		if ($res) {
			$this->file['tmp_name'] = $path;
		}
		return $res;
	}

	public function getStream() {
		return fopen($this->getPath());
	}
}