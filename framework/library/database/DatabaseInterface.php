<?php
/**
 * 数据库接口类
 * 
 * @author ShuangYa
 * @package pkgist
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2018 ShuangYa
 */

namespace yesf\library\database;

interface DababaseInterface {
	/**
	 * 实例化
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config);
	/**
	 * 断开当前连接
	 * @access public
	 */
	public function close();
	/**
	 * 设置连接信息
	 * @access public
	 * @param array $config
	 */
	public function set(array $config);
	/**
	 * 根据配置连接到数据库
	 * @access public
	 */
	public function connect();
	/**
	 * 执行查询并返回结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @param boolean $tryAgain 发生“MySQL has gone away”或类似错误时是否重试
	 * @return array
	 */
	public function query(string $sql, $data = NULL, $tryAgain = TRUE);
	/**
	 * 执行查询并返回一条结果
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get(string $sql, $data = NULL);
	/**
	 * 获取最后一个插入的ID
	 * @access public
	 * @return string
	 */
	public function getLastId();
}