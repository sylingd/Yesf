<?php
/**
 * 数据库接口类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Library
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

namespace yesf\library\database;

interface DatabaseInterface {
	/**
	 * 实例化
	 * 
	 * @access public
	 * @param array $config
	 */
	public function __construct(array $config);
	/**
	 * 执行查询并返回结果
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function query(string $sql, $data = NULL);
	/**
	 * 执行查询并返回一条结果
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @return array
	 */
	public function get(string $sql, $data = NULL);
	/**
	 * 执行查询并返回一条结果中的一列
	 * 可以只传入前两个参数，而不传入$column，此时$data将会当做$column处理
	 * 
	 * @access public
	 * @param string $sql SQL语句
	 * @param array $data 参数预绑定
	 * @param string $column 列名
	 * @return array
	 */
	public function getColumn(string $sql, $data = NULL, $column = NULL);
}