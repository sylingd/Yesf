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
	
	public function open(string $save_path, string $session_name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function destroy(string $session_id) {
		if (is_file($this->path . 'sess_' . $session_id)) {
			unlink($this->path . 'sess_' . $session_id);
		}

		return true;
	}

	public function gc(int $maxlifetime) {
		foreach (glob($this->path . '/sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
	}

	public function read(string $session_id) {
		return (string) file_get_contents($this->path . 'sess_' . $session_id);
	}

	public function write(string $session_id, string $session_data) {
		return file_put_contents($this->path . 'sess_' . $session_id, $session_data) !== false;
	}
}
