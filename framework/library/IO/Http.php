<?php
/**
 * 异步抓取HTTP页面
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Base
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\IO;
use \Swoole\Async;

class Http {
	public static $cache_timeout = 3600; //IP缓存时长
	protected static $cached_ip = [];
	protected static $cached_ip_at = [];

	public static $timeout = 5; // timeout
	public static $keep_alive = TRUE; //keep_alive

	/**
	 * 检查某个域名是否有对应IP缓存
	 * @param string $domain
	 * @return mixed
	 */
	public static function isResoluted($domain) {
		if (!isset(self::$cached_ip[$domain])) {
			return FALSE;
		}
		if (self::$cached_ip_at[$domain] - time() > self::$cache_timeout) {
			unset(self::$cached_ip[$domain]);
			unset(self::$cached_ip_at[$domain]);
			return FALSE;
		}
		return self::$cached_ip[$domain];
	}

	/**
	 * 异步获取IP
	 * @param string $domain
	 * @param callable $callback
	 * @param bool $refresh 是否强制刷新
	 */
	public static function resolute($domain, $callback, $refresh = false) {
		if (!$refresh) {
			if (isset(self::$cached_ip[$domain]) && self::$cached_ip_at[$domain] - time() <= self::$cache_timeout) {
				$callback(self::$cached_ip[$domain]);
			}
		}
		Async::dnsLookup($domain, function ($domainName, $ip) {
			self::$cached_ip[$domain] = $ip;
			self::$cached_ip_at[$domain] = time();
			$callback($ip);
		});
	}

	/**
	 * 获取初始化后的Client
	 * @param string $ip
	 * @param int $port
	 * @param bool $ssl
	 * @return \swoole_http_client
	 */
	public static function getClient($ip, $port, $ssl = FALSE) {
		$c = new \swoole_http_client($ip, $port, $ssl);
		$c->set([
			'timeout' => self::$timeout,
			'keep_alive' => self::$keep_alive
		]);
		return $c;
	}
}