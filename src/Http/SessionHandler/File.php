<?php
/**
 * 请求分发类
 *
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\Http\SessionHandler;

use SessionHandlerInterface;
use Yesf\Yesf;

class File implements SessionHandlerInterface {
	/** @var string $path Session file directory */
	private $path;

	public function __construct() {
		$this->path = Yesf::app()->getConfig('session.path');
		if ($this->path === '@TMP') {
			$this->path = sys_get_temp_dir() . '/' . uniqid();
		}
		if (strpos($this->path, '@APP') === 0) {
			$this->path = APP_PATH . substr($this->path, 5);
		}
		if (!is_dir($this->path)) {
			mkdir($this->path, 0777, true);
		}
		if (substr($this->path, -1) !== '/') {
			$this->path .= '/';
		}
	}
	
	public function open($save_path, $session_name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function destroy($session_id) {
		if (is_file($this->path . 'sess_' . $session_id)) {
			unlink($this->path . 'sess_' . $session_id);
		}

		return true;
	}

	public function gc($maxlifetime) {
		foreach (glob($this->path . '/sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
	}

	public function read($session_id) {
		if (is_file($this->path . 'sess_' . $session_id)) {
			return (string) file_get_contents($this->path . 'sess_' . $session_id);
		} else {
			return '';
		}
	}

	public function write($session_id, $session_data) {
		return file_put_contents($this->path . 'sess_' . $session_id, $session_data) !== false;
	}
}
