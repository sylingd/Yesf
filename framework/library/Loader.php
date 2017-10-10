<?php
/**
 * 自动加载相关类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library;

use \yesf\Yesf;

class Loader {
	const FILE_EXT = '.php';
	// PSR-4
	private static $prefixLengthsPsr4 = [];
	private static $prefixDirsPsr4 = [];
	private static $fallbackDirsPsr4 = [];
	// PSR-0
	private static $prefixesPsr0 = [];
	private static $fallbackDirsPsr0 = [];

	private static $useIncludePath = false;
	private static $classMap = [];
	/**
	 * Autoload
	 */
	public static function autoload($className) {
		if ($file = self::findFile($className)) {
			__include_file($file);
		}
	}
	/**
	 * @param array $classMap Class to filename map
	 */
	public static function addClassMap(array $classMap) {
		self::$classMap = array_merge(self::$classMap, $classMap);
	}
	/**
	 * Registers a set of PSR-0 directories for a given prefix, either
	 * appending or prepending to the ones previously set for this prefix.
	 *
	 * @param string $prefix  The prefix
	 * @param array|string $paths   The PSR-0 root directories
	 * @param bool $prepend Whether to prepend the directories
	 */
	public static function addPsr0($prefix, $paths, $prepend = false) {
		if (!$prefix) {
			if ($prepend) {
				self::$fallbackDirsPsr0 = array_merge((array)$paths, self::$fallbackDirsPsr0);
			} else {
				self::$fallbackDirsPsr0 = array_merge(self::$fallbackDirsPsr0, (array)$paths);
			}
			return;
		}
		$first = $prefix[0];
		if (!isset(self::$prefixesPsr0[$first][$prefix])) {
			self::$prefixesPsr0[$first][$prefix] = (array) $paths;
			return;
		}
		if ($prepend) {
			self::$prefixesPsr0[$first][$prefix] = array_merge((array)$paths, self::$prefixesPsr0[$first][$prefix]);
		} else {
			self::$prefixesPsr0[$first][$prefix] = array_merge(self::$prefixesPsr0[$first][$prefix], (array)$paths);
		}
	}
	/**
	 * Registers a set of PSR-4 directories for a given namespace, either
	 * appending or prepending to the ones previously set for this namespace.
	 *
	 * @param string $prefix  The prefix/namespace, with trailing '\\'
	 * @param array|string $paths   The PSR-4 base directories
	 * @param bool $prepend Whether to prepend the directories
	 */
	public static function addPsr4($prefix, $paths, $prepend = false) {
		if (!$prefix) {
			// Register directories for the root namespace.
			if ($prepend) {
				self::$fallbackDirsPsr4 = array_merge((array)$paths, self::$fallbackDirsPsr4);
			} else {
				self::$fallbackDirsPsr4 = array_merge(self::$fallbackDirsPsr4, (array)$paths);
			}
		} elseif (!isset(self::$prefixDirsPsr4[$prefix])) {
			// Register directories for a new namespace.
			$length = strlen($prefix);
			if ('\\' !== $prefix[$length - 1]) {
				throw new \Exception("A non-empty PSR-4 prefix must end with a namespace separator.");
			}
			self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
			self::$prefixDirsPsr4[$prefix] = (array) $paths;
		} elseif ($prepend) {
			// Prepend directories for an already registered namespace.
			self::$prefixDirsPsr4[$prefix] = array_merge((array)$paths,  self::$prefixDirsPsr4[$prefix]);
		} else {
			// Append directories for an already registered namespace.
			self::$prefixDirsPsr4[$prefix] = array_merge(self::$prefixDirsPsr4[$prefix], (array)$paths);
		}
	}
	/**
	 * 查找文件
	 * @param string $class 类名称
	 * @return string
	 */
	public static function findFile($class) {
		if ('\\' == $class[0]) {
			$class = substr($class, 1);
		}
		// class map lookup
		if (isset(self::$classMap[$class])) {
			return self::$classMap[$class];
		}
		// PSR-4 lookup
		$logicalPathPsr4 = strtr($class, '\\', '/') . self::FILE_EXT;
		$first = $class[0];
		if (isset(self::$prefixLengthsPsr4[$first])) {
			foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {
				if (0 === strpos($class, $prefix)) {
					foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
						if (file_exists($file = $dir . '/' . substr($logicalPathPsr4, $length))) {
							return $file;
						}
					}
				}
			}
		}	
		// PSR-4 fallback dirs
		foreach (self::$fallbackDirsPsr4 as $dir) {
			if (file_exists($file = $dir . '/' . $logicalPathPsr4)) {
				return $file;
			}
		}
		// PSR-0 lookup
		if (false !== $pos = strrpos($class, '\\')) {
			// namespaced class name
			$logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1) . strtr(substr($logicalPathPsr4, $pos + 1), '_', '/');
		} else {
			// PEAR-like class name
			$logicalPathPsr0 = strtr($class, '_', '/') . self::FILE_EXT;
		}
		if (isset(self::$prefixesPsr0[$first])) {
			foreach (self::$prefixesPsr0[$first] as $prefix => $dirs) {
				if (0 === strpos($class, $prefix)) {
					foreach ($dirs as $dir) {
						if (file_exists($file = $dir . '/' . $logicalPathPsr0)) {
							return $file;
						}
					}
				}
			}
		}
		// PSR-0 fallback dirs
		foreach (self::$fallbackDirsPsr0 as $dir) {
			if (file_exists($file = $dir . '/' . $logicalPathPsr0)) {
				return $file;
			}
		}
		if (!isset($file) || $file === null) {
			// Remember that this class does not exist.
			return self::$classMap[$class] = false;
		}
	}
	/**
	 * 注册composer支持
	 * @param string $dir composer根目录
	 */
	public static function addComposer($vendor_dir) {
		if (is_file($vendor_dir . 'composer/autoload_namespaces.php')) {
			$map = require($vendor_dir . 'composer/autoload_namespaces.php');
			foreach ($map as $namespace => $path) {
				self::addPsr0($namespace, $path);
			}
		}
		if (is_file($vendor_dir . 'composer/autoload_psr4.php')) {
			$map = require($vendor_dir . 'composer/autoload_psr4.php');
			foreach ($map as $namespace => $path) {
				self::addPsr4($namespace, $path);
			}
		}
		if (is_file($vendor_dir . 'composer/autoload_classmap.php')) {
			$classMap = require($vendor_dir . 'composer/autoload_classmap.php');
			if ($classMap) {
				self::addClassMap($classMap);
			}
		}
		if (is_file($vendor_dir . 'composer/autoload_files.php')) {
			$includeFiles = require($vendor_dir . 'composer/autoload_files.php');
			foreach ($includeFiles as $fileIdentifier => $file) {
				__include_file($file);
			}
		}
	}
	/**
	 * 注册自动加载
	 * @codeCoverageIgnore
	 */
	public static function register() {
		self::addPsr4('yesf\\', substr(YESF_ROOT, 0, strlen(YESF_ROOT) - 1));
		if (defined('VENDOR_PATH')) {
			self::addComposer(VENDOR_PATH);
		}
		//注册自动加载
		spl_autoload_register('yesf\\library\\Loader::autoload', TRUE, TRUE);
	}
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function __include_file($file) {
	return include($file);
}