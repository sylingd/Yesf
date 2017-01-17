<?php
/**
 * 控制台
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Tool
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

class Client {
	public $client;
	public function __construct($ip, $port) {
		$this->client = new swoole_client(SWOOLE_TCP);
		$this->client->set([
			'open_length_check' => 1,
			'package_length_type' => 'N',
			'package_length_offset' => 0,
			'package_body_offset' => 4,
			'package_max_length' => 2000000,
			'open_tcp_nodelay' => 1
		]);
		if ($this->client->connect($ip, $port, 1)) {
			throw new \Exception('Can not connect to server');
		}
	}
	public function close() {
		$this->client->close();
	}
	public function send($action, $data = []) {
		$data['action'] = $action;
		$sendStr = json_encode($data);
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		return $this->client->send($sendData);
	}
	public function recv() {
		$data = $this->client->recv();
		return json_decode(substr($data, 4), 1);
	}
}